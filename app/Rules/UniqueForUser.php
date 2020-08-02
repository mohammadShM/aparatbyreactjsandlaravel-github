<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueForUser implements Rule
{
    /**
     * @var string
     */
    private $tableName;
    /**
     * @var string|null
     */
    private $columnName;
    /**
     * @var string|null
     */
    private $userId;
    /**
     * @var string
     */
    private $userIdField;

    /**
     * Create a new rule instance.
     *
     * @param string $tableName
     * @param string|null $columnName
     * @param string|null $userId
     * @param string $userIdField
     */
    public function __construct(string $tableName, string $columnName = null, string $userId = null,
                                $userIdField = 'user_id')
    {

        $this->tableName = $tableName;
        $this->columnName = $columnName;
        $this->userId = $userId ?? auth()->id();
        $this->userIdField = $userIdField;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $field = !empty($this->columnName) ? $this->columnName : $attribute;
        $count = DB::table($this->tableName)
            ->where($field, $value)
            ->where($this->userIdField, $this->userId)
            ->count();
        return $count === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This value already exists for this user.';
    }
}
