<?php

namespace App\Traits;

/**
 * Trait HasBlindIndex
 *
 * Implementa o padrão de "Blind Index" para permitir buscas em campos criptografados.
 *
 * PROBLEMA: Campos criptografados com AES-256 (Laravel 'encrypted' cast) não podem
 * ser consultados diretamente no banco de dados — cada registro tem um valor cifrado diferente.
 *
 * SOLUÇÃO: Para cada campo sensível (ex: 'email'), mantemos um campo paralelo de hash
 * (ex: 'email_search') com SHA-256 do valor em minúsculas + APP_KEY como pepper.
 * Isso permite buscar por hash sem expor o dado real.
 *
 * CONFORMIDADE: Atende à LGPD — em caso de vazamento do banco, os hashes são inúteis
 * sem a APP_KEY, e os dados reais estão criptografados com AES-256.
 *
 * @see https://paragonie.com/blog/2017/05/building-searchable-encrypted-databases-with-php-and-sql
 */
trait HasBlindIndex
{
    /**
     * Boot the trait and hook into saving event.
     */
    protected static function bootHasBlindIndex()
    {
        static::saving(function ($model) {
            foreach ($model->getBlindIndexFields() as $field => $searchField) {
                if ($model->isDirty($field)) {
                    $model->{$searchField} = static::generateBlindIndex($model->{$field});
                }
            }
        });
    }

    /**
     * Generate a deterministic hash for searching.
     */
    public static function generateBlindIndex($value)
    {
        if (empty($value)) return null;
        // Normaliza o valor (minúsculas, sem espaços) antes de gerar o hash.
        // APP_KEY age como "pepper" — torna os hashes inúteis sem a chave da aplicação.
        // Atenção: se a APP_KEY mudar, TODOS os blind indexes precisam ser regerados
        // via: php artisan secure:data
        return hash('sha256', strtolower(trim($value)) . config('app.key'));
    }

    /**
     * Define which fields have blind indexes.
     */
    abstract public function getBlindIndexFields(): array;
}
