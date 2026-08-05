<?php
namespace tests\DuckPhp\Component;

use DuckPhp\Component\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Validator::class);

        // —— init / 链式 API ——
        $v = Validator::_(new Validator());
        $this->assertSame($v, $v->init([]));
        $this->assertSame($v, $v->init(['a' => 'required'], new \stdClass()));
        $this->assertSame($v, $v->setRules([]));
        $this->assertSame($v, $v->setMessage([]));
        $this->assertSame($v, $v->setExceptionClass(\Exception::class));
        $this->assertSame($v, $v->skipEmpty());
        $this->assertSame($v, $v->skipEmpty(false));

        // —— valid()：全部通过 ——
        $rules = [
            'a_required' => 'required',
            'a_nullable' => 'nullable|email',
            'a_email' => 'email',
            'a_url' => 'url',
            'a_int' => 'int',
            'a_integer' => 'integer',
            'a_numeric' => 'numeric',
            'a_string' => 'string',
            'a_array' => 'array',
            'a_bool' => 'bool',
            'a_min' => 'min:5',
            'a_max' => 'max:10',
            'a_between' => 'between:1,10',
            'a_minLen' => 'minLen:3',
            'a_maxLen' => 'maxLen:10',
            'a_length' => 'length:5',
            'a_in' => 'in:red,green',
            'a_confirmed' => 'confirmed',
            'a_regex' => 'regex:/^\d+$/',
            'a_date' => 'date',
            'a_json' => 'json',
            'a_callback' => 'callback:'.ValidatorTest::class.'::checkOk',
        ];
        $data_ok = [
            'a_required' => 'x',
            'a_nullable' => '',           // nullable 空值跳过 email
            'a_email' => 'a@b.com',
            'a_url' => 'https://example.com',
            'a_int' => '123',
            'a_integer' => 123,
            'a_numeric' => '12.5',
            'a_string' => 'str',
            'a_array' => [1, 2],
            'a_bool' => 'true',
            'a_min' => 6,
            'a_max' => 9,
            'a_between' => 5,
            'a_minLen' => 'abc',
            'a_maxLen' => 'abc',
            'a_length' => 'abcde',
            'a_in' => 'green',
            'a_confirmed' => 'pwd',
            'a_confirmed_confirmation' => 'pwd',
            'a_regex' => '123',
            'a_date' => '2024-01-01',
            'a_json' => '{"a":1}',
            'a_callback' => 'x',
        ];
        $v->setRules($rules)->skipEmpty();
        $this->assertSame([], $v->valid($data_ok));

        // —— valid()：失败场景，逐字段报错 ——
        $data_bad = [
            'a_required' => '',
            'a_nullable' => 'bad-email',  // 非空 → email 校验失败
            'a_email' => 'bad',
            'a_url' => 'bad',
            'a_int' => 'abc',
            'a_integer' => '123',         // 字符串非 is_int
            'a_numeric' => 'abc',
            'a_string' => 123,
            'a_array' => 'notarray',
            'a_bool' => 'xxx',
            'a_min' => 3,
            'a_max' => 12,
            'a_between' => 11,
            'a_minLen' => 'ab',
            'a_maxLen' => 'abcdefghijk',
            'a_length' => 'abcd',
            'a_in' => 'blue',
            'a_confirmed' => 'pwd',
            'a_confirmed_confirmation' => 'pwd2',
            'a_regex' => 'abc',
            'a_date' => 'notadate',
            'a_json' => 'notjson',
            'a_callback' => 'y',          // 回调 checkOk 对非 x 返回 false
        ];
        $errors = $v->valid($data_bad);
        foreach (array_keys($rules) as $field) {
            $this->assertArrayHasKey($field, $errors, $field);
        }

        // —— 类型安全：数组值不触发 warning，直接失败 ——
        $this->assertArrayHasKey('a_min', $v->valid(['a_min' => [1, 2]]));
        $this->assertArrayHasKey('a_minLen', $v->valid(['a_minLen' => [1, 2]]));
        $this->assertArrayHasKey('a_in', $v->valid(['a_in' => [1, 2]]));
        $this->assertArrayHasKey('a_json', $v->valid(['a_json' => [1, 2]]));
        $this->assertArrayHasKey('a_regex', $v->valid(['a_regex' => [1, 2]]));

        // —— skipEmpty(false)：空值参与校验 ——
        $v->setRules(['a' => 'email'])->skipEmpty(false);
        $errors = $v->valid(['a' => '']);
        $this->assertArrayHasKey('a', $errors);

        // —— nullable + skipEmpty(false)：空值仍豁免 ——
        $v->setRules(['a' => 'nullable|email'])->skipEmpty(false);
        $this->assertSame([], $v->valid(['a' => '']));

        // —— check()：失败抛异常（默认 \Exception） ——
        $v->setRules(['a' => 'required'])->setExceptionClass(\Exception::class);
        try {
            $v->check(['a' => '']);
            $this->fail('expected exception');
        } catch (\Exception $ex) {
            $this->assertStringContainsString('Field [a]', $ex->getMessage());
        }
        // check() 通过不抛异常
        $v->check(['a' => 'x']);

        // —— check()：setExceptionClass 自定义异常类 ——
        $v->setRules(['a' => 'required'])->setExceptionClass(ValidatorTestException::class);
        $caught = false;
        try {
            $v->check(['a' => '']);
        } catch (ValidatorTestException $ex) {
            $caught = true;
        }
        $this->assertTrue($caught);

        // —— filter()：验证 + 白名单过滤 ——
        $out = $v->setRules(['a' => 'required'])->filter(['a' => 'x', 'b' => 'y']);
        $this->assertSame(['a' => 'x'], $out);
        try {
            $v->filter(['a' => '']);
            $this->fail('expected exception');
        } catch (ValidatorTestException $ex) {
        }

        // —— 自定义消息 messages ——
        $v->setRules(['a' => 'required'])
            ->setMessage(['a.required' => '自定义必填消息']);
        $errors = $v->valid(['a' => '']);
        $this->assertSame(['a' => '自定义必填消息'], $errors);

        // —— 规则串解析：空段跳过 ——
        $v->setRules(['a' => 'required||email']);
        $this->assertSame([], $v->valid(['a' => 'x@y.com']));

        // —— 未知规则抛异常 ——
        try {
            $v->setRules(['a' => 'require'])->valid(['a' => 1]);
            $this->fail('expected exception');
        } catch (\InvalidArgumentException $ex) {
            $this->assertStringContainsString('Unknown validator rule', $ex->getMessage());
        }

        \LibCoverage\LibCoverage::End();
    }
    public static function checkOk($value, $data)
    {
        return $value === 'x';
    }
}
class ValidatorTestException extends \Exception
{
}
