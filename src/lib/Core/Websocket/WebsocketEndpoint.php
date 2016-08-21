<?php
/**
 * Created by PhpStorm.
 * User: lelouch
 * Date: 30/6/16
 * Time: 10:19 PM
 */

namespace Leloutama\lib\Core\Websocket;
use Leloutama\lib\Core\Interfaces\Endpoint;

class WebsocketHandler implements Endpoint {
    /* Frame control bits */
    const FIN      = 0b1;
    const RSV_NONE = 0b000;
    const OP_CONT  = 0x00;
    const OP_TEXT  = 0x01;
    const OP_BIN   = 0x02;
    const OP_CLOSE = 0x08;
    const OP_PING  = 0x09;
    const OP_PONG  = 0x0A;
    const CONTROL = 1;
    const DATA = 2;
    const ERROR = 3;

    public function serve() {
        // TODO: Implement serve() method.
    }

    /**
     * Thanks to Amphp/Aerys
     * From Amphp/Aerys
     * A webserver built upon Amp concurrency framework.
     * https://github.com/amphp/aerys
     *
     * A stateful generator websocket frame parser
     *
     * @param callable $emitCallback A callback to receive parser event emissions
     * @param array $options Optional parser settings
     * @return \Generator
     */
    static public function parser(callable $emitCallback, array $options = []): \Generator {
        $callbackData = $options["cb_data"] ?? null;
        $emitThreshold = $options["threshold"] ?? 32768;
        $maxFrameSize = $options["max_frame_size"] ?? PHP_INT_MAX;
        $maxMsgSize = $options["max_msg_size"] ?? PHP_INT_MAX;
        $textOnly = $options["text_only"] ?? false;
        $doUtf8Validation = $validateUtf8 = $options["validate_utf8"] ?? false;
        $dataMsgBytesRecd = 0;
        $nextEmit = $emitThreshold;
        $dataArr = [];
        $buffer = yield;
        $offset = 0;
        $bufferSize = \strlen($buffer);
        $frames = 0;
        while (1) {
            if ($bufferSize < 2) {
                $buffer = substr($buffer, $offset);
                $offset = 0;
                do {
                    $buffer .= yield $frames;
                    $bufferSize = \strlen($buffer);
                    $frames = 0;
                } while ($bufferSize < 2);
            }
            $firstByte = ord($buffer[$offset]);
            $secondByte = ord($buffer[$offset + 1]);
            $offset += 2;
            $bufferSize -= 2;
            $fin = (bool)($firstByte & 0b10000000);
            // $rsv = ($firstByte & 0b01110000) >> 4; // unused (let's assume the bits are all zero)
            $opcode = $firstByte & 0b00001111;
            $isMasked = (bool)($secondByte & 0b10000000);
            $maskingKey = null;
            $frameLength = $secondByte & 0b01111111;
            $isControlFrame = $opcode >= 0x08;
            if ($validateUtf8 && $opcode !== self::OP_CONT && !$isControlFrame) {
                $doUtf8Validation = $opcode === self::OP_TEXT;
            }
            if ($frameLength === 0x7E) {
                if ($bufferSize < 2) {
                    $buffer = substr($buffer, $offset);
                    $offset = 0;
                    do {
                        $buffer .= yield $frames;
                        $bufferSize = \strlen($buffer);
                        $frames = 0;
                    } while ($bufferSize < 2);
                }
                $frameLength = unpack('n', $buffer[$offset] . $buffer[$offset + 1])[1];
                $offset += 2;
                $bufferSize -= 2;
            } elseif ($frameLength === 0x7F) {
                if ($bufferSize < 8) {
                    $buffer = substr($buffer, $offset);
                    $offset = 0;
                    do {
                        $buffer .= yield $frames;
                        $bufferSize = \strlen($buffer);
                        $frames = 0;
                    } while ($bufferSize < 8);
                }
                $lengthLong32Pair = unpack('N2', substr($buffer, $offset, 8));
                $offset += 8;
                $bufferSize -= 8;
                if (PHP_INT_MAX === 0x7fffffff) {
                    if ($lengthLong32Pair[1] !== 0 || $lengthLong32Pair[2] < 0) {
                        $code = Code::MESSAGE_TOO_LARGE;
                        $errorMsg = 'Payload exceeds maximum allowable size';
                        break;
                    }
                    $frameLength = $lengthLong32Pair[2];
                } else {
                    $frameLength = ($lengthLong32Pair[1] << 32) | $lengthLong32Pair[2];
                    if ($frameLength < 0) {
                        $code = Code::PROTOCOL_ERROR;
                        $errorMsg = 'Most significant bit of 64-bit length field set';
                        break;
                    }
                }
            }
            if ($frameLength > 0 && !$isMasked) {
                $code = Code::PROTOCOL_ERROR;
                $errorMsg = 'Payload mask required';
                break;
            } elseif ($isControlFrame) {
                if (!$fin) {
                    $code = Code::PROTOCOL_ERROR;
                    $errorMsg = 'Illegal control frame fragmentation';
                    break;
                } elseif ($frameLength > 125) {
                    $code = Code::PROTOCOL_ERROR;
                    $errorMsg = 'Control frame payload must be of maximum 125 bytes or less';
                    break;
                }
            } elseif (($opcode === 0x00) === ($dataMsgBytesRecd === 0)) {
                // We deliberately do not accept a non-fin empty initial text frame
                $code = Code::PROTOCOL_ERROR;
                if ($opcode === 0x00) {
                    $errorMsg = 'Illegal CONTINUATION opcode; initial message payload frame must be TEXT or BINARY';
                } else {
                    $errorMsg = 'Illegal data type opcode after unfinished previous data type frame; opcode MUST be CONTINUATION';
                }
                break;
            } elseif ($maxFrameSize && $frameLength > $maxFrameSize) {
                $code = Code::MESSAGE_TOO_LARGE;
                $errorMsg = 'Payload exceeds maximum allowable frame size';
                break;
            } elseif ($maxMsgSize && ($frameLength + $dataMsgBytesRecd) > $maxMsgSize) {
                $code = Code::MESSAGE_TOO_LARGE;
                $errorMsg = 'Payload exceeds maximum allowable message size';
                break;
            } elseif ($textOnly && $opcode === 0x02) {
                $code = Code::UNACCEPTABLE_TYPE;
                $errorMsg = 'BINARY opcodes (0x02) not accepted';
                break;
            }
            if ($isMasked) {
                if ($bufferSize < 4) {
                    $buffer = substr($buffer, $offset);
                    $offset = 0;
                    do {
                        $buffer .= yield $frames;
                        $bufferSize = \strlen($buffer);
                        $frames = 0;
                    } while ($bufferSize < 4);
                }
                $maskingKey = substr($buffer, $offset, 4);
                $offset += 4;
                $bufferSize -= 4;
            }
            if ($bufferSize >= $frameLength) {
                if (!$isControlFrame) {
                    $dataMsgBytesRecd += $frameLength;
                }
                $payload = substr($buffer, $offset, $frameLength);
                $offset += $frameLength;
                $bufferSize -= $frameLength;
            } else {
                if (!$isControlFrame) {
                    $dataMsgBytesRecd += $bufferSize;
                }
                $frameBytesRecd = $bufferSize;
                $payload = substr($buffer, $offset);
                do {
                    // if we want to validate UTF8, we must *not* send incremental mid-frame updates because the message might be broken in the middle of an utf-8 sequence
                    // also, control frames always are <= 125 bytes, so we never will need this as per https://tools.ietf.org/html/rfc6455#section-5.5
                    if (!$isControlFrame && $dataMsgBytesRecd >= $nextEmit) {
                        if ($isMasked) {
                            $payload ^= str_repeat($maskingKey, ($frameBytesRecd + 3) >> 2);
                            // Shift the mask so that the next data where the mask is used on has correct offset.
                            $maskingKey = substr($maskingKey . $maskingKey, $frameBytesRecd % 4, 4);
                        }
                        if ($dataArr) {
                            $dataArr[] = $payload;
                            $payload = implode($dataArr);
                            $dataArr = [];
                        }
                        if ($doUtf8Validation) {
                            $string = $payload;
                            /* TODO: check how many bits are set to 1 instead of multiple (slow) preg_match()es and substr()s */
                            for ($i = 0; !preg_match('//u', $payload) && $i < 8; $i++) {
                                $payload = substr($payload, 0, -1);
                            }
                            if ($i == 8) {
                                $code = Code::INCONSISTENT_FRAME_DATA_TYPE;
                                $errorMsg = 'Invalid TEXT data; UTF-8 required';
                                break 2;
                            }
                            $emitCallback([self::DATA, $payload, false], $callbackData);
                            $payload = $i > 0 ? substr($string, -$i) : '';
                        } else {
                            $emitCallback([self::DATA, $payload, false], $callbackData);
                            $payload = '';
                        }
                        $frameLength -= $frameBytesRecd;
                        $nextEmit = $dataMsgBytesRecd + $emitThreshold;
                        $frameBytesRecd = 0;
                    }
                    $buffer = yield $frames;
                    $bufferSize = \strlen($buffer);
                    $frames = 0;
                    if ($bufferSize + $frameBytesRecd >= $frameLength) {
                        $dataLen = $frameLength - $frameBytesRecd;
                    } else {
                        $dataLen = $bufferSize;
                    }
                    if (!$isControlFrame) {
                        $dataMsgBytesRecd += $dataLen;
                    }
                    $payload .= substr($buffer, 0, $dataLen);
                    $frameBytesRecd += $dataLen;
                } while ($frameBytesRecd != $frameLength);
                $offset = $dataLen;
                $bufferSize -= $dataLen;
            }
            if ($isMasked) {
                // This is memory hungry but it's ~70x faster than iterating byte-by-byte
                // over the masked string. Deal with it; manual iteration is untenable.
                $payload ^= str_repeat($maskingKey, ($frameLength + 3) >> 2);
            }
            if ($fin || $dataMsgBytesRecd >= $emitThreshold) {
                if ($isControlFrame) {
                    $emit = [self::CONTROL, $payload, $opcode];
                } else {
                    if ($dataArr) {
                        $dataArr[] = $payload;
                        $payload = implode($dataArr);
                        $dataArr = [];
                    }
                    if ($doUtf8Validation) {
                        if ($fin) {
                            $i = preg_match('//u', $payload) ? 0 : 8;
                        } else {
                            $string = $payload;
                            for ($i = 0; !preg_match('//u', $payload) && $i < 8; $i++) {
                                $payload = substr($payload, 0, -1);
                            }
                            if ($i > 0) {
                                $dataArr[] = substr($string, -$i);
                            }
                        }
                        if ($i == 8) {
                            $code = Code::INCONSISTENT_FRAME_DATA_TYPE;
                            $errorMsg = 'Invalid TEXT data; UTF-8 required';
                            break;
                        }
                    }
                    $emit = [self::DATA, $payload, $fin];
                    if ($fin) {
                        $dataMsgBytesRecd = 0;
                    }
                    $nextEmit = $dataMsgBytesRecd + $emitThreshold;
                }
                $emitCallback($emit, $callbackData);
            } else {
                $dataArr[] = $payload;
            }
            $frames++;
        }
        // An error occurred...
        // stop parsing here ...
        /** @noinspection PhpUndefinedVariableInspection */
        $emitCallback([self::ERROR, $errorMsg, $code], $callbackData);
        yield $frames;
        while (1) {
            yield 0;
        }
    }
}