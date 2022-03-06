<?php

namespace CCUFFS\Scrap;

use PHPHtmlParser\Dom;
use Composer\CaBundle\CaBundle;

class AcademicCalendarUFFS {
    private const UFFS_ACADEMIC_CALENDARS_URLS = 'https://www.uffs.edu.br/institucional/pro-reitorias/graduacao/calendario-academico';
    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            \GuzzleHttp\RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath()
        ]);
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
    public function parseCalendarByUrl($url) {
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
    
            if ($isEventRow) {
                echo 'Event: ' . $rowContent . PHP_EOL;
                $period = trim($columns[0]->innerText);
                $event = trim($columns[1]->innerText);
                
                $data[$currentMonth]['events'][] = [
                    'period' => $period,
                    'event' => $event
                ];
            }
    
            if ($isCalendarRow) {
                echo 'Calendar: ' . $rowContent . PHP_EOL;
                echo 'Event: ' . $rowContent . PHP_EOL;
    
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
                $data[$currentMonth] = [
                    'month' => $this->findMonthContent($currentMonth, $months),
                    'events' => [],
                    'festivities' => []
                ];
                continue;
            }
        }
    
        return $data;
        var_dump($data);
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
    
            echo 'Fetching ' . $url . ': ' . $text . PHP_EOL;
    
            $data = $this->parseCalendarByUrl($url);
    
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