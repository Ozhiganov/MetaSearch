<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LanguageController extends Controller
{
    public function createOverview(Request $request)
    {
        $languageFilePath = resource_path() . "/lang/";
        $files            = scandir($languageFilePath);
        $dirs             = [];
        foreach ($files as $file) {
            if (is_dir($languageFilePath . $file) && $file !== "." && $file !== "..") {
                $dirs[] = $file;
            }

        }
        # Im Array "$dirs" haben wir nun alle Verzeichnisse mit dem entsprechenden Sprachkürzel
        # Alle von uns bislang unterstützen Sprachen sind hier eingetragen.
        $langTexts = [];
        $sum       = [];
        foreach ($dirs as $dir) {
            # Wir überprüfen nun für jede Datei die Anzahl der vorhandenen Übersetzungen
            $di                           = new RecursiveDirectoryIterator($languageFilePath . $dir);
            $langTexts[$dir]["textCount"] = 0;
            $langTexts[$dir]["fileCount"] = 0;
            foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
                if (!$this->endsWith($filename, ".")) {
                    $langTexts[$dir]["fileCount"] += 1;
                    $tmp = include $filename;
                    foreach ($tmp as $key => $value) {
                        $sum = array_merge($sum, $this->getValues([$key => $value], basename($filename)));
                    }
                    $langTexts[$dir]["textCount"] += count($tmp);
                }

            }
        }
        $deComplete = $langTexts["de"]["textCount"] === count($sum) ? true : false;
        return view('languages.overview')
            ->with('title', trans('titles.languages'))
            ->with('langTexts', $langTexts)
            ->with('sum', $sum)
            ->with('deComplete', $deComplete);
    }

    public function createEditPage($from, $to, $exclude = "")
    {
        $languageFilePath = resource_path() . "/lang/";
        $files            = scandir($languageFilePath);
        $dirs             = [];
        foreach ($files as $file) {
            if (is_dir($languageFilePath . $file) && $file !== "." && $file !== "..") {
                $dirs[$file] = $file;
            }

        }
        # Abbruchbedingungen:
        if ($from === "" || $to === "" || ($from !== "de" && $from !== "all") || ($from === "all" && $to !== "de") && !array_has($dirs, $to)) {
            return redirect(url('languages'));
        }

        $texts = [];

        $langTexts = [];
        $sum       = [];
        $filePath  = [];
        foreach ($dirs as $dir) {
            if ($from !== "all" && $dir !== $to && $dir !== $from) {
                continue;
            }

            # Wir überprüfen nun für jede Datei die Anzahl der vorhandenen Übersetzungen
            $di              = new RecursiveDirectoryIterator($languageFilePath . $dir);
            $langTexts[$dir] = 0;
            foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
                if (!$this->endsWith($filename, ".")) {
                    $tmp = include $filename;
                    foreach ($tmp as $key => $value) {
                        $sum                                    = array_merge($sum, $this->getValues([$key => $value], basename($filename)));
                        $texts[basename($filename)][$key][$dir] = $value;
                    }
                    $filePath[basename($filename)] = preg_replace("/lang\/.*?\//si", "lang/$to/", substr($filename, strpos($filename, "lang")));
                    $langTexts[$dir] += count($tmp);
                }

            }
        }
        $langs = [];
        $fn    = "";
        $t     = [];
        $ex    = ['files' => [], 'new' => 0];
        if ($exclude !== "") {
            try {
                $ex = unserialize(base64_decode($exclude));
            } catch (\ErrorException $e) {
                $ex = ['files' => [], 'new' => 0];
            }
        }
        foreach ($texts as $filename => $text) {
            $has = false;
            foreach ($ex['files'] as $file) {
                if ($file === $filename) {
                    $has = true;
                }
            }
            if ($has) {
                continue;
            }

            # Hier können wir später die bereits bearbeiteten Dateien ausschließen.
            foreach ($text as $textname => $languages) {

                foreach ($languages as $lang => $value) {
                    if ($lang !== $to) {
                        $langs = array_add($langs, $lang, $lang);
                    }

                }
                if (!isset($languages[$to])) {
                    $fn = $filePath[$filename];
                    $t  = $text;
                    break;
                }
            }

        }

        while ($this->hasToMuchDimensions($t)) {
            $t = $this->deMultidimensionalizeArray($t);
        }

        #die(var_dump($t));

        return view('languages.edit')
            ->with('texts', $t)
            ->with('filename', $fn)
            ->with('title', trans('titles.languages.edit'))
            ->with('langs', $langs)
            ->with('to', $to)
            ->with('langTexts', $langTexts)
            ->with('sum', $sum)
            ->with('css', 'editLanguage.css')
            ->with('new', $ex["new"]);
    }

    private function getValues($values, $prefix = "")
    {
        $return = [];
        if (!is_array($values)) {
            return $return;
        } else {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $return = array_merge($return, $this->getValues($value, $prefix . $key));
                } elseif (is_string($value)) {
                    $return[$prefix . $key] = $value;
                }
            }
        }

        return $return;
    }

    private function hasToMuchDimensions($t)
    {
        foreach ($t as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $lang => $val) {
                    if (is_array($val)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function deMultidimensionalizeArray($t)
    {
        # Es gibt noch Besonderheiten in den Texten
        # Es kann sein, dass statt einem String ein Array aus Strings als Werte existieren.
        # Diese müssen aufgelöst werden:
        $tmp = [];
        foreach ($t as $key => $value) {
            $isArray = false;
            if (is_array($value)) {
                foreach ($value as $lang => $val) {
                    if (is_array($val)) {
                        $isArray = true;
                    }

                }
            } else {
                $tmp[$key] = $value;
                continue;
            }
            if (!$isArray) {
                $tmp[$key] = $value;
            } else {
                $tmp[$key] = "";
                foreach ($value as $lang => $val) {
                    if (is_array($val)) {
                        foreach ($val as $key2 => $val) {
                            $tmp["\t" . $key . "#" . $key2][$lang] = $val;
                        }

                    }
                }
            }

        }
        return $tmp;
    }

    private function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    private function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }
}