<?php

namespace App\Http\Controllers;

use App\Restaurants;
use Illuminate\Http\Request;
use Sunra\PhpSimple\HtmlDomParser;

class HomeController extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $postcode = $request->postcode;

        if (preg_match("/^[\\d]{6}$/", $postcode)) {

            $param = $this->getLocationParam($request, $postcode);

            $string = HtmlDomParser::file_get_html("https://www.foodpanda.sg/restaurants?$param");

            $data = [];

            $insertMessage = '';

            $count = 0;
            $valCount = 1;


            foreach ($string->find('section[class=js-infscroll-load-more-here] article') as $content) {

                $count++;
                $valCount++;
                $data[] = $this->htmlData($content, $postcode);
                if ($request->insertdatabase == 'true') {
                    if (Restaurants::where('postal_code', $postcode)->count() < $valCount) {
                        Restaurants::create($data[$count - 1]);
                        $message = "Inserted To Database";
                    } else {
                        $message = "Record Already exist";
                    }
                }

            }
            return $this->responseData($request, $data, 200, $message);

        } else {
            return $this->responseData($request, '', 404);
        }

    }

    /**
     * @param $content
     * @param $postCode
     * @return array
     */
    private function htmlData($content, $postCode)
    {
        $linkTag = $content->find('a', 0);
        $imageTag = $content->find('div[class=vendor__image] img', 0);
        $titleTag = $content->find('div[class=vendor__details] div[class=vendor__title] span[class=vendor__name]', 0);
        $category = $content->find('div[class=vendor__details] ul[class=vendor__cuisines] li', 0);
        $deliTime = $content->find('div[class=vendor__info] ul[class=vendor__availability] li[class=vendor__delivery-time] span[class=delivery-time-label] span[class=minutes]', 0);

        $array = [
            'name' => $titleTag->innertext,
            'logo' => $imageTag->attr['src'],
            'detail_link' => 'www.foodpanda.sg/' . $linkTag->attr['href'],
            'category' => $category->innertext,
            'delivery_duration' => $deliTime->innertext,
            'postal_code' => $postCode,
        ];

        return $array;
    }


    /**
     * @param $request
     * @param $postcode
     * @return mixed|string
     */
    private function getLocationParam($request, $postcode)
    {
        $postcodeUrl = json_decode(file_get_contents('https://www.foodpanda.sg/location-suggestions-ajax?address=' . $postcode));

        $param = '';

        foreach ($postcodeUrl as $code) {
            if ($code->value == $postcode) {
                $lat = $code->fillSearchFormOnSelect->lat;
                $lng = $code->fillSearchFormOnSelect->lng;
                $street = str_replace(' ', '+', $code->fillSearchFormOnSelect->street);
                $houseNumber = str_replace(' ', '+', $code->fillSearchFormOnSelect->houseNumber);
                $extendedDetailsId = $code->fillSearchFormOnSelect->extendedDetailsId;
                $trackingId = $code->fillSearchFormOnSelect->tracking_id;
                $postalcode = $code->value;
                $param = "lat=$lat&lng=$lng&street=$street&houseNumber=$houseNumber&extendedDetailsId=$extendedDetailsId&tracking_id=$trackingId&postcode=$postalcode";

            } else {
                return $this->responseData($request, '', '400');
            }
            return $param;
        }


    }


    /**
     * @param $request
     * @param $data
     * @param $type
     * @param string $insertMessage
     * @return mixed
     */
    private function responseData($request, $data, $type, $insertMessage = '')
    {
        $now = microtime(true);
        $responseTime = (float)sprintf("%.3f", ($now - LARAVEL_START));
        $path = $request->getRequestUri();
        $method = $request->method();

        // Can also handle other type of Response
        if ($type == '200') {
            return response()->json(array(
                'success' => 1,
                'code' => 200,
                'meta' => array('method' => $method,
                    'endpoint' => $path),
                'data' => $data,
                'errors' => '{}',
                'duration' => $responseTime,
                'database' => $insertMessage
            ));
        }
        if ($type == '404') {
            return response()->json(array(
                'success' => 0,
                'code' => 404,
                'meta' => array('method' => $method,
                    'endpoint' => $path),
                'data' => $data,
                'errors' => array('message' => 'Invalid Singapore Postal Code', 'code' => '404'),
                'duration' => $responseTime,
            ));
        }
        if ($type == '400') {
            return response()->json(array(
                'success' => 0,
                'code' => 400,
                'meta' => array('method' => $method,
                    'endpoint' => $path),
                'data' => $data,
                'errors' => array('message' => 'Area Does Not Exist', 'code' => '204'),
                'duration' => $responseTime,
            ))->send();
        }

    }

}