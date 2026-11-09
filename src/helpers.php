<?php

function mb_ucfirst(string $str): string
{
    if ($str === '') return $str;
    return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
}
