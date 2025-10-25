<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Assert\Exceptions;

/**
 * @author Brian Faust <brian@cline.sh>
 */
enum ValidationError: int
{
    case InvalidInteger = 10;
    case InvalidFloat = 9;
    case InvalidDigit = 11;
    case InvalidIntegerish = 12;
    case InvalidBoolean = 13;
    case InvalidScalar = 209;
    case InvalidString = 16;
    case InvalidNumeric = 23;
    case InvalidResource = 243;
    case InvalidArray = 24;
    case InvalidObject = 207;
    case InvalidCallable = 215;
    case InvalidIterable = 239;
    case InvalidInstanceOfAny = 240;
    case InvalidAnyOf = 241;
    case InvalidNotA = 242;
    case ValueEmpty = 14;
    case ValueNull = 15;
    case ValueNotNull = 25;
    case ValueNotEmpty = 205;
    case InvalidNotBlank = 27;
    case InvalidRegex = 17;
    case InvalidNotRegex = 50;
    case InvalidMinLength = 18;
    case InvalidMaxLength = 19;
    case InvalidStringStart = 20;
    case InvalidStringEnd = 238;
    case InvalidStringContains = 21;
    case InvalidStringNotContains = 229;
    case InvalidLength = 37;
    case InvalidAlnum = 31;
    case InvalidChoice = 22;
    case InvalidKeyExists = 26;
    case InvalidKeyNotExists = 216;
    case InvalidCount = 41;
    case InvalidTraversable = 44;
    case InvalidArrayAccessible = 45;
    case InvalidKeyIsset = 46;
    case InvalidValueInArray = 47;
    case InvalidCountable = 226;
    case InvalidMinCount = 227;
    case InvalidMaxCount = 228;
    case InvalidUniqueValues = 230;
    case InvalidList = 231;
    case InvalidMap = 232;
    case InvalidCountBetween = 233;
    case InvalidArrayKey = 234;
    case InvalidInstanceOf = 28;
    case InvalidSubclassOf = 29;
    case InvalidClass = 105;
    case InvalidInterface = 106;
    case InterfaceNotImplemented = 202;
    case InvalidNotInstanceOf = 204;
    case InvalidMethod = 208;
    case InvalidProperty = 224;
    case InvalidPropertyNotExists = 225;
    case InvalidMethodNotExists = 244;
    case InvalidRange = 30;
    case InvalidMin = 35;
    case InvalidMax = 36;
    case InvalidLess = 210;
    case InvalidLessOrEqual = 211;
    case InvalidGreater = 212;
    case InvalidGreaterOrEqual = 213;
    case InvalidBetween = 219;
    case InvalidBetweenExclusive = 220;
    case InvalidPositiveInteger = 235;
    case InvalidNatural = 236;
    case InvalidEq = 33;
    case InvalidSame = 34;
    case InvalidNotEq = 42;
    case InvalidNotSame = 43;
    case InvalidTrue = 32;
    case InvalidFalse = 38;
    case InvalidNotFalse = 39;
    case InvalidFile = 102;
    case InvalidDirectory = 101;
    case InvalidReadable = 103;
    case InvalidWriteable = 104;
    case InvalidEmail = 201;
    case InvalidUrl = 203;
    case InvalidUuid = 40;
    case InvalidIp = 218;
    case InvalidE164 = 48;
    case InvalidBase64 = 49;
    case InvalidJsonString = 206;
    case InvalidDate = 214;
    case InvalidSatisfy = 217;
    case InvalidThrows = 245;
    case InvalidExtension = 222;
    case InvalidConstant = 221;
    case InvalidVersion = 223;
}
