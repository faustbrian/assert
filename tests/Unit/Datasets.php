<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Carbon\Carbon;
use Cline\Assert\Assertion;
use Tests\Fixtures\OneCountable;

// Datasets for AssertTest

dataset('dataInvalidFloat', fn (): array => [
    [1],
    [false],
    ['test'],
    [null],
    ['1.23'],
    ['10'],
]);

dataset('dataInvalidInteger', fn (): array => [
    [1.23],
    [false],
    ['test'],
    [null],
    ['1.23'],
    ['10'],
    [Carbon::now()],
]);

dataset('dataValidIntergerish', fn (): array => [
    [10],
    ['10'],
    [-10],
    ['-10'],
    [0o123],
    ['0123'],
    [0],
    ['0'],
    [0o0_123],
    ['00123'],
    [0o0],
    ['00'],
    ['0040'],
]);

dataset('dataInvalidIntegerish', fn (): array => [
    'A float' => [1.23],
    'Boolean true' => [true],
    'Boolean false' => [false],
    'A text string' => ['test'],
    'A null' => [null],
    'A float in a string' => ['1.23'],
    'A negative float in a string' => ['-1.23'],
    'A file pointer' => [fopen(__FILE__, 'rb')],
    'A float in a string with a leading space' => [' 1.23'],
    'An integer in a string with a leading space' => [' 123'],
    'A negative integer in a string with a leading space' => [' -123'],
    'An integer in a string with a trailing space' => ['456 '],
    'A negative integer in a string with a trailing space' => ['-456 '],
    'An array' => [[]],
    'An object' => [new stdClass()],
    'A float that is less than 1' => [0.1],
    'A float that is less than 0.1' => [0.01],
    'A float that is less than 0.01' => [0.001],
    'A float in a string that is less than 1' => ['0.1'],
    'A float in a string that is less than 0.1' => ['0.01'],
    'A float in a string that is less than 0.01' => ['0.001'],
    'An empty string' => [''],
    'A single space string' => [' '],
    'A multiple spaced string' => ['  '],
]);

dataset('dataInvalidNotEmpty', fn (): array => [
    [''],
    [false],
    [0],
    [null],
    [[]],
]);

dataset('dataInvalidEmpty', fn (): array => [
    ['foo'],
    [true],
    [12],
    [['foo']],
    [new stdClass()],
]);

dataset('dataInvalidNull', fn (): array => [
    ['foo'],
    [''],
    [false],
    [12],
    [0],
    [[]],
]);

dataset('dataInvalidString', fn (): array => [
    [1.23],
    [false],
    [new ArrayObject()],
    [null],
    [10],
    [true],
]);

dataset('dataInvalidArray', fn (): array => [
    [null],
    [false],
    ['test'],
    [1],
    [1.23],
    [new stdClass()],
    [fopen('php://memory', 'rb')],
]);

dataset('dataInvalidUniqueValues', function (): array {
    $object = new stdClass();

    return [
        [['foo' => 'bar', 'baz' => 'bar']],
        [[$object, $object]],
        [[$object, &$object]],
        [[true, true]],
        [[null, null]],
        [[1, $object, true, $object, 'foo']],
    ];
});

dataset('dataValidUniqueValues', function (): array {
    $object = new stdClass();

    return [
        [['foo' => 0, 'bar' => '0']],
        [[true, 'true', false, 'false', null, 'null']],
        [['foo', 'Foo', 'FOO']],
        [['foo', $object]],
        [[new stdClass(), new stdClass()]],
        [[&$object, new stdClass()]],
    ];
});

dataset('dataInvalidNotBlank', fn (): array => [
    [''],
    [' '],
    ["\t"],
    ["\n"],
    ["\r"],
    [false],
    [null],
    [[]],
]);

dataset('dataInvalidUrl', fn (): array => [
    ['google.com'],
    ['://google.com'],
    ['http ://google.com'],
    ['http:/google.com'],
    ['http://goog_le.com'],
    ['http://google.com::aa'],
    ['http://google.com:aa'],
    ['ftp://google.fr'],
    ['faked://google.fr'],
    ['http://127.0.0.1:aa/'],
    ['ftp://[::1]/'],
    ['http://[::1'],
    ['http://hello.☎/'],
    ['http://:password@symfony.com'],
    ['http://:password@@symfony.com'],
    ['http://username:passwordsymfony.com'],
    ['http://usern@me:password@symfony.com'],
]);

dataset('dataValidUrl', fn (): array => [
    ['http://a.pl'],
    ['http://www.google.com'],
    ['http://www.google.com.'],
    ['http://www.google.museum'],
    ['https://google.com/'],
    ['https://google.com:80/'],
    ['http://www.example.coop/'],
    ['http://www.test-example.com/'],
    ['http://www.symfony.com/'],
    ['http://symfony.fake/blog/'],
    ['http://symfony.com/?'],
    ['http://symfony.com/search?type=&q=url+validator'],
    ['http://symfony.com/#'],
    ['http://symfony.com/#?'],
    ['http://www.symfony.com/doc/current/book/validation.html#supported-constraints'],
    ['http://very.long.domain.name.com/'],
    ['http://localhost/'],
    ['http://myhost123/'],
    ['http://127.0.0.1/'],
    ['http://127.0.0.1:80/'],
    ['http://[::1]/'],
    ['http://[::1]:80/'],
    ['http://[1:2:3::4:5:6:7]/'],
    ['http://sãopaulo.com/'],
    ['http://xn--sopaulo-xwa.com/'],
    ['http://sãopaulo.com.br/'],
    ['http://xn--sopaulo-xwa.com.br/'],
    ['http://пример.испытание/'],
    ['http://xn--e1afmkfd.xn--80akhbyknj4f/'],
    ['http://مثال.إختبار/'],
    ['http://xn--mgbh0fb.xn--kgbechtv/'],
    ['http://例子.测试/'],
    ['http://xn--fsqu00a.xn--0zwm56d/'],
    ['http://例子.測試/'],
    ['http://xn--fsqu00a.xn--g6w251d/'],
    ['http://例え.テスト/'],
    ['http://xn--r8jz45g.xn--zckzah/'],
    ['http://مثال.آزمایشی/'],
    ['http://xn--mgbh0fb.xn--hgbk6aj7f53bba/'],
    ['http://실례.테스트/'],
    ['http://xn--9n2bp8q.xn--9t4b11yi5a/'],
    ['http://العربية.idn.icann.org/'],
    ['http://xn--ogb.idn.icann.org/'],
    ['http://xn--e1afmkfd.xn--80akhbyknj4f.xn--e1afmkfd/'],
    ['http://xn--espaa-rta.xn--ca-ol-fsay5a/'],
    ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
    ['http://☎.com/'],
    ['http://username:password@symfony.com'],
    ['http://user.name:password@symfony.com'],
    ['http://username:pass.word@symfony.com'],
    ['http://user.name:pass.word@symfony.com'],
    ['http://user-name@symfony.com'],
    ['http://symfony.com?'],
    ['http://symfony.com?query=1'],
    ['http://symfony.com/?query=1'],
    ['http://symfony.com#'],
    ['http://symfony.com#fragment'],
    ['http://symfony.com/#fragment'],
    ['http://symfony.com?query[]=1'],
    ['http://symfony.com/?query[]=1'],
    ['http://symfony.com?query[1]=1'],
    ['http://symfony.com/?query[2]=1'],
    ['http://symfony.com?query[1][]=1'],
    ['http://symfony.com/?query[2][]=1'],
    ['http://symfony.com?query[1][3]=1'],
    ['http://symfony.com/?query[2][4]=1'],
    ['http://symfony.com?query[1][3]=1&query[5][7]=2'],
    ['http://symfony.com/?query[2][4]=1&query[6][8]=2'],
]);

dataset('dataInvalidMin', fn (): array => [
    [0, 1],
    [0.5, 2.5],
]);

dataset('dataInvalidMax', fn (): array => [
    [2, 1],
    [2.5, 0.5],
]);

dataset('dataLengthUtf8Characters', fn (): array => [
    ['址', 1],
    ['ل', 1],
]);

dataset('isJsonStringDataprovider', fn (): array => [
    '»null« value' => [json_encode(null)],
    '»false« value' => [json_encode(false)],
    'array value' => ['["false"]'],
    'object value' => ['{"tux":"false"}'],
]);

dataset('isJsonStringInvalidStringDataprovider', fn (): array => [
    'no json string' => ['invalid json encoded string'],
    'error in json string' => ['{invalid json encoded string}'],
]);

dataset('providesValidUuids', fn (): array => [
    ['ff6f8cb0-c57d-11e1-9b21-0800200c9a66'],
    ['ff6f8cb0-c57d-21e1-9b21-0800200c9a66'],
    ['ff6f8cb0-c57d-31e1-9b21-0800200c9a66'],
    ['ff6f8cb0-c57d-41e1-9b21-0800200c9a66'],
    ['ff6f8cb0-c57d-51e1-9b21-0800200c9a66'],
    ['FF6F8CB0-C57D-11E1-9B21-0800200C9A66'],
    ['00000000-0000-0000-0000-000000000000'],
]);

dataset('providesInvalidUuids', fn (): array => [
    ['zf6f8cb0-c57d-11e1-9b21-0800200c9a66'],
    ['af6f8cb0c57d11e19b210800200c9a66'],
    ['ff6f8cb0-c57da-51e1-9b21-0800200c9a66'],
    ['af6f8cb-c57d-11e1-9b21-0800200c9a66'],
    ['3f6f8cb0-c57d-11e1-9b21-0800200c9a6'],
]);

dataset('providesValidE164s', fn (): array => [
    ['+33626525690'],
    ['33626525690'],
    ['+16174552211'],
]);

dataset('providesInvalidE164s', fn (): array => [
    ['+3362652569e'],
    ['+3361231231232652569'],
]);

dataset('dataInvalidCount', fn (): array => [
    [['Hi', 'There'], 3],
    [new OneCountable(), 2],
    [new OneCountable(), 0],
    [[], 2],
]);

dataset('dataInvalidMinCount', function () {
    yield '2 elements while at least 3 expected' => [['Hi', 'There'], 3];

    yield '1 countable while at least 2 expected' => [new OneCountable(), 2];

    yield '2 countable while at least 3 expected' => [new SimpleXMLElement('<a><b /><c /></a>'), 3];

    if (extension_loaded('intl')) {
        yield '6 countable while at least 7 expected' => [new ResourceBundle('en_US', __DIR__.'/_files/ResourceBundle'), 7];
    }
});

dataset('dataInvalidMaxCount', function () {
    yield '2 elements while at most 1 expected' => [['Hi', 'There'], 1];

    yield '1 countable while at most 0 expected' => [new OneCountable(), 0];

    yield '2 countable while at most 1 expected' => [new SimpleXMLElement('<a><b /><c /></a>'), 1];

    if (extension_loaded('intl')) {
        yield '6 countable while at most 5 expected' => [new ResourceBundle('en_US', __DIR__.'/_files/ResourceBundle'), 5];
    }
});

dataset('invalidChoicesProvider', fn (): array => [
    'empty values' => [[], ['tux'], Assertion::VALUE_EMPTY],
    'empty recodes in $values' => [['tux' => ''], ['tux'], Assertion::VALUE_EMPTY],
]);

dataset('invalidLessProvider', fn (): array => [
    [2, 1],
    [2, 2],
    ['aaa', 'aaa'],
    ['aaaa', 'aaa'],
    [Carbon::today(), Carbon::yesterday()],
    [Carbon::today(), Carbon::today()],
]);

dataset('invalidLessOrEqualProvider', fn (): array => [
    [2, 1],
    ['aaaa', 'aaa'],
    [Carbon::today(), Carbon::yesterday()],
]);

dataset('invalidGreaterProvider', fn (): array => [
    [1, 2],
    [2, 2],
    ['aaa', 'aaa'],
    ['aaa', 'aaaa'],
    [Carbon::yesterday(), Carbon::today()],
    [Carbon::today(), Carbon::today()],
]);

dataset('validDateProvider', fn (): array => [
    ['2012-03-13', 'Y-m-d'],
    ['29.02.2012 12:03:36.432563', 'd.m.Y H:i:s.u'],
    ['13.08.2015 17:08:23 Thu Thursday th 224 August Aug 8 15 17 432563 UTC UTC', 'd.m.Y H:i:s D l S z F M n y H u e T'],
    ['1439486158', 'U'],
]);

dataset('invalidGreaterOrEqualProvider', fn (): array => [
    [1, 2],
    ['aaa', 'aaaa'],
    [Carbon::yesterday(), Carbon::tomorrow()],
]);

dataset('invalidDateProvider', fn (): array => [
    ['this is not the date', 'Y-m-d'],
    ['2011-02-29', 'Y-m-d'],
    ['2012.02.29 12:60:36.432563', 'Y.m.d H:i:s.u'],
]);

dataset('validIpProvider', fn (): array => [
    ['0.0.0.0'],
    ['14.32.152.216'],
    ['255.255.255.255'],
    ['2001:db8:85a3:8d3:1319:8a2e:370:7348'],
]);

dataset('invalidIpProvider', fn (): array => [
    ['invalid ip address'],
    ['14.32.152,216'],
    ['14.32.256.216'],
    ['192.168.0.10', \FILTER_FLAG_NO_PRIV_RANGE],
    ['127.0.0.1', \FILTER_FLAG_NO_RES_RANGE],
    ['2001:db8:85a3:8d3:1319:8g2e:370:7348'],
    ['fdb9:75b9:9e69:5d08:1:1:1:1', \FILTER_FLAG_NO_PRIV_RANGE],
]);

dataset('providerInvalidBetween', fn (): array => [
    [1, 2, 3],
    [3, 1, 2],
    ['aaa', 'bbb', 'ccc'],
    ['ddd', 'bbb', 'ccc'],
    [Carbon::yesterday(), Carbon::today(), Carbon::tomorrow()],
    [Carbon::tomorrow(), Carbon::yesterday(), Carbon::today()],
]);

dataset('providerValidBetween', fn (): array => [
    [2, 1, 3],
    [1, 1, 1],
    ['bbb', 'aaa', 'ccc'],
    ['aaa', 'aaa', 'aaa'],
    [Carbon::today(), Carbon::yesterday(), Carbon::tomorrow()],
    [Carbon::today(), Carbon::today(), Carbon::today()],
]);

dataset('providerInvalidBetweenExclusive', fn (): array => [
    [1, 1, 2],
    [2, 1, 2],
    ['aaa', 'aaa', 'bbb'],
    ['bbb', 'aaa', 'bbb'],
    [Carbon::today(), Carbon::today(), Carbon::tomorrow()],
    [Carbon::tomorrow(), Carbon::today(), Carbon::tomorrow()],
]);

dataset('providerValidBetweenExclusive', fn (): array => [
    [2, 1, 3],
    ['bbb', 'aaa', 'ccc'],
    [Carbon::today(), Carbon::yesterday(), Carbon::tomorrow()],
]);

dataset('invalidPropertiesExistProvider', fn (): array => [
    [['invalidProperty']],
    [['invalidProperty', 'anotherInvalidProperty']],
]);

dataset('invalidEqArraySubsetProvider', fn (): array => [
    'firstArgumentNotArray' => ['notArray', []],
    'secondArgumentNotArray' => [[], 'notArray'],
]);
