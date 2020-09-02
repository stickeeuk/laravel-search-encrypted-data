<?php

namespace Stickee\LaravelSearchEncryptedData\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 */
class TestSoftDeleteModel extends TestModel
{
    use SoftDeletes;
}
