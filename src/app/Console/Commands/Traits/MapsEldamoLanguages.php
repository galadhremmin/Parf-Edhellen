<?php

namespace App\Console\Commands\Traits;

use App\Models\Language;

/**
 * Translates Eldamo's language codes to Parf Edhellen language IDs. Shared by every importer that
 * reads Eldamo's dataset, so that the glossary and the phrases agree on what "mq" means.
 */
trait MapsEldamoLanguages
{
    private ?array $_eldamoLanguageMap = null;

    private function getLanguageMap()
    {
        if (is_array($this->_eldamoLanguageMap)) {
            return $this->_eldamoLanguageMap;
        }

        // Establish a language mapping between Eldamo and Parf Edhellen. This map is based on
        // Eldamo's XSD (xs:simpleType name="language-type") for v0.5.5
        $languageMap = [
            'ad' => 'adunaic',
            'aq' => 'ancient quenya',
            'at' => 'ancient telerin',
            'av' => 'avarin',
            'bel' => 0, // _beleriandic_ not supported
            'bs' => 'black speech',
            'cir' => 0, // _cirth_ not supported
            'dan' => 'ossriandric', // <~~ deviation from "danian"!
            'dun' => 'dunlending',
            'dor' => 'doriathrin',
            'eas' => 'easterling',
            'ed' => 'edain',
            'edan' => 0,
            'eilk' => 'early ilkorin',
            'en' => 'early noldorin',
            'ent' => 'entish',
            'eon' => 0, // 'early old noldorin',
            'eoq' => 0, // 'early old qenya',
            'ep' => 'early primitive elvish',
            'eq' => 'early quenya',
            'et' => 'solosimpi', // 'early telerin',
            'fal' => 'doriathrin', // 'falathrin',
            'g' => 'gnomish',
            'ilk' => 'doriathrin', // <~~ deviation from "ilkorin"!
            'kh' => 'khuzdul',
            'khx' => 'khuzdul', // <~~ deviation from "Khuzdul, External"!
            'lem' => 'lemberin',
            'ln' => 'noldorin', // <~~ deviation from "late noldorin"!
            'lon' => 'old noldorin', // <~~ deviation from "late old noldorin"!
            'mp' => 'middle primitive elvish',
            'mq' => 'qenya', // <~~ deviation from "middle quenya"!
            'mt' => 'middle telerin',
            'n' => 'noldorin',
            'oss' => 'ossriandric',
            'p' => 'primitive elvish',
            'pad' => 'primitive adunaic',
            'nan' => 'nandorin',
            'ns' => 'north sindarin',
            'on' => 'old noldorin',
            'os' => 'old sindarin',
            'q' => 'quenya',
            'roh' => 'rohirric',
            's' => 'sindarin',
            'sar' => 0, // _sarati_ not supported
            'sol' => 'solosimpi',
            't' => 'telerin',
            'tal' => 'taliska',
            'teng' => 0, // _tengwar_ not supported
            'un' => 'undetermined',
            'val' => 'valarin',
            'van' => 'quendya', // vanyarin
            'wes' => 'westron',
            'wos' => 'wose',
            'maq' => 'middle ancient quenya',
            'norths' => 'north sindarin',
        ];

        $missing = [];
        foreach ($languageMap as $key => $id) {
            if (is_numeric($id)) {
                continue;
            }

            $language = Language::where('name', $id)
                ->select('id')
                ->first();

            if (! $language) {
                $missing[] = $id;

                continue;
            }

            $languageMap[$key] = $language->id;
        }

        if (! empty($missing)) {
            $this->error('Missing the languages: "'.implode('", "', $missing).'". Can\'t proceed.');
            exit;
        }

        $this->_eldamoLanguageMap = $languageMap;

        return $languageMap;
    }

    private function getNeoLanguageMap()
    {
        $languageMap = $this->getLanguageMap();

        return [
            'ns' => $languageMap['s'],
            'nq' => $languageMap['q'],
            'np' => $languageMap['p'],
        ];
    }
}
