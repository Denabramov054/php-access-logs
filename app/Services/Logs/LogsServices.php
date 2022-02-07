<?php
namespace App\Services\Logs;

use Illuminate\Support\Collection;

class LogsServices
{
    protected $path;
    protected $regular;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->regular = [
            'ip' => '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}',
            'status' => '" \d{1,3} ',
            'views' => '\d{1,9} "',
            'url' => '"http(.*?)"',
            'browser' => '\" \"(.*?)\"$',
        ];
    }

    /**
     * @return array
     */
    public function make(): array
    {
        $collection = $this->parserLogs();
        return [
            'views' => $collection->count(),
            'urls' => $collection->unique('url')->count(),
            'traffic' => $collection->pluck('views')->sum(),
            'crawlers' => $collection->groupBy('browser')->map(function ($browser) {
                    return $browser->count();
            }),
            'statusCodes' => $collection->groupBy('status')->map(function ($status) {
                    return $status->count();
            }),
        ];
    }

    /**
     * @param array $result
     * @return Collection
     */
    protected function formatterCollect(array $result): Collection
    {
        $collection = collect();
        collect($result)->map(function ($item) use($collection) {
            $collection->push([
                'ip' => $item[0]['ip'],
                'status' => (int) preg_replace('~\D+~','',$item[1]['status']),
                'views' => (int) preg_replace('~\D+~','',$item[2]['views']),
                'url' => $item[3]['url'],
                'browser' => strtok(substr($item[4]['browser'], 3), '/'),
            ]);
        });
        return $collection;
    }

    /**
     * @return Collection
     */
    protected function parserLogs(): Collection
    {
        $result = [];
        while (($line = fgets($this->path)) !== false) {
            $item = [];
            foreach ($this->regular as $key => $value) {
                if (preg_match_all('/' . $value . '/', $line, $matches)) {
                    array_push($item, [
                        $key => $matches[0][0]
                    ]);
                }
            }
            array_push($result, $item);
        }
        return $this->formatterCollect($result);
    }
}
