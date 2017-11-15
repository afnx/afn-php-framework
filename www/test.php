<?php

/*
 * This file was created by AFN.
 * If you think that there is a notifiable issue
 * affecting the file, please contact AFN.
 * AFN <afn@alifuatnumanoglu.com>
 */

class test {

    protected static $testParam = array("testa");

    public static function testStaticFunc($par) {
        self::$testParam[] = $par;
    }

    public function echoFunc() {
        print_r(self::$testParam);
    }

}

$testA = new test();

test::testStaticFunc("test1");
test::testStaticFunc("test2");
test::testStaticFunc("test3");

$testA->echoFunc();