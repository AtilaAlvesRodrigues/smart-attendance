<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Unicidade de um campo criptografado, conferida pelo blind index (*_search).
 * Uma regra 'unique' comum nunca funcionaria: o texto cifrado muda a cada gravação.
 */
class UnicoPorBlindIndex implements ValidationRule
{
    /**
     * @param  class-string  $modelo  modelo com o trait HasBlindIndex
     */
    public function __construct(
        private string $modelo,
        private string $colunaBusca,
        private ?int $ignorarId = null,
        private string $mensagem = 'Este valor já está cadastrado.',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existe = $this->modelo::withTrashed()
            ->where($this->colunaBusca, $this->modelo::generateBlindIndex((string) $value))
            ->when($this->ignorarId, fn ($q) => $q->whereKeyNot($this->ignorarId))
            ->exists();

        if ($existe) {
            $fail($this->mensagem);
        }
    }
}
