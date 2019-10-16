<?php

namespace App\Utilites;

use Carbon\Carbon;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Class XMLExporter
 * @package App\Utilites
 */
class XMLExporter
{
    const DATE_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * @var array|null
     */
    private $rawData = null;
    /**
     * @var null
     */
    private $data = null;
    /**
     * @var Carbon|null
     */
    private $start = null;
    /**
     * @var Carbon|null
     */
    private $end = null;

    /**
     * XMLExporter constructor.
     * @param array $rawData
     * @param Carbon|null $start
     * @param Carbon|null $end
     */
    public function __construct(array $rawData, Carbon $start = null, Carbon $end = null)
    {
        $this->rawData = $rawData;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function asXml()
    {
        $this->generate();

        return ArrayToXml::convert(
            $this->data,
            $this->root(),
            true,
            'UTF-8'
        );
    }

    /**
     * @return array
     */
    private function root(): array
    {
        return [
            'rootElementName' => 'exportData',
            '_attributes' => [
                'version' => '1.0',
                'dateCreate' => Carbon::now()->format(self::DATE_FORMAT),
                'periodStart' => $this->start ? $this->start->format(self::DATE_FORMAT) : '',
                'periodEnd' => $this->end ? $this->end->format(self::DATE_FORMAT) : '',
            ]
        ];
    }

    /**
     * Generate export data
     */
    private function generate(): void
    {
        $this->data = $this->rawData;
        $this->prepareHistorySection();
    }

    /**
     * Prepare section history for xml convert
     */
    private function prepareHistorySection()
    {
        if ($this->data['history']) {
            $this->data['history'] = [
                'element' => $this->data['history'],
            ];
        } else {
            unset($this->data['history']);
        }
    }
}
