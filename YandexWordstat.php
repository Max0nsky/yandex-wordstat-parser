<?php

class YandexWordstat
{
    public $token;

    const API_LINK = "https://api.direct.yandex.ru/v4/json/";

    const MAX_TRIES = 60;
    const SLEEP_TIME = 1;

    function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Запускает на сервере формирование отчета о статистике поисковых запросов.
     * 
     * Отчеты удаляются автоматически через 5 часов после формирования. 
     * Удалять отчеты вручную следует в случае, когда сформировано максимальное 
     * количество отчетов (пять) и нужно сформировать новый отчет.
     */
    public function createNewWordstatReport(array $phrases, array $regions)
    {
        $method = 'CreateNewWordstatReport';
        $params = [
            'Phrases' => $phrases,
            'GeoID' => $regions
        ];

        $reports = $this->getWordstatReportList();
        if (isset($reports->data) && (count($reports->data) == 5)) {
            $this->deleteWordstatReport($reports->data[0]->ReportID);
        }

        return $this->sendQuery($method, $params);
    }

    /**
     * Удаляет отчет о статистике поисковых запросов.
     */
    public function deleteWordstatReport(int $id)
    {
        $method = 'DeleteWordstatReport';
        $params = $id;

        return $this->sendQuery($method, $params);
    }

    /**
     * Возвращает отчет о статистике поисковых запросов.
     */
    public function getWordstatReport(int $id)
    {
        $max_tries = self::MAX_TRIES;
        while ($max_tries > 0) {
            $report = $this->getOneInListReport($id);
            if (!empty($report)) {
                if ($report->StatusReport !== "Done") {
                    sleep(self::SLEEP_TIME);
                    $max_tries--;
                } else {
                    break;
                }
            }
        }

        if ($max_tries === 0) {
            throw new Exception('Отчет не был сформирован в течение определенного времени.');
        }

        $method = 'GetWordstatReport';
        $params = $id;

        return $this->sendQuery($method, $params);
    }

    /**
     * Возвращает отчет о статистике поисковых запросов.
     */
    public function getWordstatReportList()
    {
        $method = 'GetWordstatReportList';
        $params = "";

        return $this->sendQuery($method, $params);
    }

    public function getOneInListReport(int $id)
    {
        $reports = $this->getWordstatReportList();
        foreach ($reports->data as $report) {
            if (($report->ReportID == $id)) {
                return $report;
            }
        }

        return NULL;
    }

    private function utf8($struct)
    {
        if (is_array($struct)) {
            foreach ($struct as $key => $value) {
                $struct[$key] = $this->utf8($value);
            }
        } elseif (is_string($struct)) {
            $struct = utf8_encode($struct);
        }
        return $struct;
    }

    private function sendQuery($method, $params)
    {
        $request = [
            'token' => $this->token,
            'method' => $method,
            'param' => $this->utf8($params),
            'locale' => 'ru',
        ];

        $opts = [
            'http' => [
                'method' => "POST",
                'content' => json_encode($request),
            ]
        ];

        $context = stream_context_create($opts);
        $result = file_get_contents(self::API_LINK, 0, $context);
        $result = json_decode($result);

        if (isset($result->error_code)) {
            throw new Exception('Код ошибки API Яндекс.Директ: ' . $result->error_code);
        }

        return $result;
    }
}
