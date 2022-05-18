<?php

namespace \uzdevid\cardlink;

use yii\base\Component;
use yii\base\InvalidValueException;

class CardLink extends Component {
    private $token;
    public $shop_id;

    public function getBill() {
        return new Bill();
    }

    public function getExecute() {
        return new Execute();
    }
}

class Bill extends CardLink {
    public function create($data) {
        if (empty($data['amount']))
            throw new InvalidValueException('amount is empty');

        if (empty($data['type']))
            $data['type'] = 'normal';

        if (empty($data['shop_id']))
            $data['shop_id'] = $this->shop_id;

        if (empty($data['currency_in']))
            $data['currency_in'] = 'RUB';

        $data['amount'] = number_format($data['amount'], 2, '.', '');
        return $this->execute->query('/api/v1/bill/create', $data);
    }
}

class Execute extends CardLink {
    public $url = 'https://cardlink.link';

    public function query($method, $body, $type = 'POST') {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $type,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->token}",
                "Content-Type: application/x-www-form-urlencoded"
            ],
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }
}