<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ZxcvbnPhp\Zxcvbn;

class ZxcvbnPassword implements ValidationRule
{
    protected int $minScore;

    public function __construct(int $minScore = 3)
    {
        $this->minScore = $minScore;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $zxcvbn = new Zxcvbn();
        $strength = $zxcvbn->passwordStrength($value);

        if ($strength['score'] < $this->minScore) {
            $fail('Password yang Anda masukkan terlalu lemah atau terlalu umum digunakan. Coba kombinasi yang lebih panjang atau lebih acak.');
        }
    }
}
