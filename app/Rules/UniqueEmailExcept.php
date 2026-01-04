<?php

// app/Rules/UniqueEmailExcept.php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueEmailExcept implements Rule
{
    protected $table;
    protected $column;
    protected $valueToIgnore;
    protected $ignoreColumn;

    public function __construct($table, $column, $valueToIgnore, $ignoreColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->valueToIgnore = $valueToIgnore;
        $this->ignoreColumn = $ignoreColumn;
    }

    public function passes($attribute, $value)
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where($this->ignoreColumn, '!=', $this->valueToIgnore);

        return $query->count() === 0;
    }

    public function message()
    {
        return 'このメールアドレスは既に使用されています。';
    }


}
