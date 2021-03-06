<?php

namespace CCUFFS\Scrap;

use PHPHtmlParser\Dom;
use Composer\CaBundle\CaBundle;

class AcademicCalendarUFFS {
    private const UFFS_ACADEMIC_CALENDARS_URLS = 'https://www.uffs.edu.br/institucional/pro-reitorias/graduacao/calendario-academico';
    private $client;
    private $debug;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            \GuzzleHttp\RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath()
        ]);
        $this->debug = false;
    }

    private function debug($text) {
        if ($this->debug) {
            echo $text . PHP_EOL;
        }
    }

    private function findMonthContent($text, $months) {
        foreach ($months as $month) {
            if (stripos($text, $month) !== false) {
                return $month;
            }
        }
        return false;
    }
    
    private function containsAny($str, array $arr) {
        foreach ($arr as $a) {
            if (stripos($str,$a) !== false) return true;
        }
        return false;
    }
    
    private function containsAll($str, array $arr) {
        $found = 0;
        foreach ($arr as $a) {
            if (stripos($str,$a) !== false) $found++;
        }
        return $found == count($arr);
    }

    /**
     * 
     */
    public function setDebug($value) {
        $this->debug = $value;
    }
    
    /**
     * 
     */
    public function fetchCalendarByUrl($url) {
        $dom = new Dom();
        $dom->loadFromUrl($url, null, $this->client);
    
        $months = [
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ];
    
        $weekDaysSample = [
            'Dom',
            'Seg',
            'Ter',
            'Qua',
            'Qui',
            'Sex'
        ];
    
        $data = [];
        $currentMonth = '';
        $rows = $dom->find('tr');
    
        foreach ($rows as $row) {
            $columns = $row->find('td');
            $howManyColumns = count($columns);
            $rowContent = trim($row->innerText);
    
            $isMonthRow    = $howManyColumns == 1 && $this->containsAny($rowContent, $months);
            $isCalendarRow = $howManyColumns > 2 && $this->containsAll($rowContent, $weekDaysSample);
            $isEventRow    = $howManyColumns == 2 && !$this->containsAll($rowContent, $weekDaysSample);
    
            if ($isEventRow && $currentMonth != '') {
                $this->debug('Event: ' . $rowContent);
                $period = trim($columns[0]->innerText);
                $event = trim($columns[1]->innerText);
                
                $data[$currentMonth]['events'][] = [
                    'period' => $period,
                    'event' => $event
                ];
            }
    
            if ($isCalendarRow && $currentMonth != '') {
                $this->debug('Calendar: ' . $rowContent);
                $this->debug('Event: ' . $rowContent);
    
                $lastColumn = $columns[count($columns) - 1];
                $entries = $lastColumn->getChildren();
    
                foreach ($entries as $entry) {
                    $festivity = trim($entry->text());
    
                    if (!empty($festivity)) {
                        $data[$currentMonth]['festivities'][] = $festivity;
                    }
                }
            }
    
            if ($isMonthRow) {
                $currentMonth = trim($row->innerText);

                if (empty($currentMonth)) {
                    continue;
                }

                $data[$currentMonth] = [
                    'month' => $this->findMonthContent($currentMonth, $months),
                    'events' => [],
                    'festivities' => []
                ];
                continue;
            }
        }
    
        $monthsFound = count(array_keys($data));

        if ($monthsFound < 6) {
            // Provavelmente não é um calendário acadêmico com apenas 6 meses
            return [];
        }

        return $data;
    }
    
    /**
     * 
     */
    public function findCalendarEntries() {
        $urls = [];
    
        $dom = new Dom();
        $dom->loadFromUrl(self::UFFS_ACADEMIC_CALENDARS_URLS, null, $this->client);
    
        $paragraphs = $dom->find('#content p');
    
        foreach ($paragraphs as $p) {
            $text = $p->innerText;
            $links = $p->find('a');
    
            if (count($links) == 0) {
                continue;
            }
    
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                $urls[] = [
                    'text' => $text,
                    'url' => $href
                ];
            }
        }
    
        // sort urls by year
        usort($urls, function($a, $b) {
            $a = explode('/', $a['url']);
            $b = explode('/', $b['url']);
            $a = $a[count($a) - 1];
            $b = $b[count($b) - 1];
            return strcmp($b, $a);
        });
    
        return $urls;
    }
    
    /**
     * 
     */
    public function fetchCalendars() {
        $entries = $this->findCalendarEntries();
        $calendars = [];
    
        foreach ($entries as $entry) {
            $text = $entry['text'];
            $url = $entry['url'];
    
            $this->debug('Fetching ' . $url . ': ' . $text);
    
            $data = $this->fetchCalendarByUrl($url);
    
            if (count($data) == 0) {
                continue;
            }
    
            $calendars[] = [
                'title' => $text,
                'url' => $url,
                'data' => $data
            ];
        }
    
        return $calendars;
    }
}

?>