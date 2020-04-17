<?php

namespace App\Entity\Translation;

interface TranslationEntityInterface
{

    /**
     * Load translated string.
     *
     * @return string|null
     */
    public function getTranslation(): ?string;

    /**
     * Load data to entity.
     * For example: imagine that entity has `domain`, `locale`, `key` and `translation` params
     * This method may be called as
     * ```
     * $entity->load([
     *    'domain' => $domain,
     *    'locale' => $locale,
     *    'key' => $key,
     *    'translation' => $translation,
     * ]);
     * ```
     * and return valid entity for store in database.
     *
     * @param array $params
     *
     * @return TranslationEntityInterface
     */
    public function load(array $params): self;
}