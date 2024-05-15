<?php

require_once 'classes/Property.php';

class PropertyController {

    private int $page = 1;
    private int $perPage = 10;
    private string|null $filter = null;
    private string|null $proximityValue = null;
    private string|null $amenitieValue = null;
    private string|null $priceValue = null;
    private string|null $roomCountValue = null;
    
    /**
     * get property data as json array
     */
    private function getProperties(){
        // get file depending on if any data has been maniupulated or not
        $fileLocation = "";
        if(file_exists( __DIR__.'/data/json/manipulated_data.json')){
            $fileLocation = __DIR__."/data/json/manipulated_data.json";
        } else {
            $fileLocation = "data/json/data.json";
        }

        return array_map(fn($item) => new Property(
            $item["Listing Title"],
            $item["Address"],
            isset($item["Lat"]) ? floatval($item["Lat"]) : 0.0,
            isset($item["Loong"]) ? floatval($item["Loong"]) : 0.0,
            $item["Amenities"],
            $item["Sold by"],
            $item["Bedrooms"],
            $item["Bathrooms"],
            $item["Reception rooms"],
            $item["Image"],
            $item["Price"]
        ), json_decode(strip_tags(str_replace(array("\r", "\n"), '', file_get_contents($fileLocation))), true));
    }

    /**
     * get paginated property list
     */
    public function getPropertyList() {
        $this->page = (isset($_GET['page']) ? $_GET['page'] : 1);
        $this->perPage = (isset($_GET['perPage']) ? $_GET['perPage'] : 10);
        $this->filter = (isset($_GET['filter']) ? $_GET['filter'] : null);
        $this->proximityValue = (isset($_GET['proximity']) ? $_GET['proximity'] : null);
        $this->amenitieValue = (isset($_GET['amenitie']) ? $_GET['amenitie'] : null);
        $this->priceValue = (isset($_GET['price']) ? $_GET['price'] : null);
        $this->roomCountValue = (isset($_GET['roomCount']) ? $_GET['roomCount'] : null);

        $properties = $this->getProperties();

        // filters
        if($this->proximityValue){
            $properties = $this->proximityFilter($this->proximityValue, $properties);
        }
        if($this->amenitieValue){
            $properties = $this->amenitiesFilter($this->amenitieValue, $properties);
        }
        if($this->priceValue){
            $properties = $this->priceFilter($this->priceValue, $properties);
        }
        if($this->roomCountValue){
            $properties = $this->roomCountFilter($this->roomCountValue, $properties);
        }

        // pagination offsets
        $startIndex = ($this->page - 1) * $this->perPage; 
        $endIndex = $startIndex + $this->perPage; 

        // echo out the JSON string
        echo json_encode([
            'data' => json_encode(array_slice($properties, $startIndex, $this->perPage)),
            'pagination' => [
                'totalItems' => count($properties),
                'totalPages' => ceil(count($properties) / $this->perPage),
                'currentPage' => $this->page
            ]
        ]);
    }

    public function getAmendCoorindates(){
        $properties = $this->getProperties();

        preg_match_all('/\b[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][ABD-HJLNP-UW-Z]{2}\b/', implode(', ', array_map(fn($property) => $property->getAddress(), $properties)), $matches);
        
        $postCodeLanLon = $this->getLatLongFromPostcode("", true, $matches[0]);

        foreach($properties as $property){
            preg_match('/\b[A-Z]{1,2}[0-9R][0-9A-Z]? [0-9][ABD-HJLNP-UW-Z]{2}\b/', $property->getAddress(), $matches);

            $postcodeData = array_values(array_filter($postCodeLanLon, function($obj) use ($matches) {
                return $obj['query'] === $matches[0];
            }));

            if($postcodeData[0]['result']){
                $property->setLat($postcodeData[0]['result']['latitude']);
                $property->setLong($postcodeData[0]['result']['longitude']);
            }
        }

        // write data to a temp file to save
        // normally this would go stright into the database with a save function on the class
        // however to keep this more compact for the test we are just saving results in a json file
        $tempFilePath = __DIR__.'/data/json/manipulated_data.json';

        // if temp data file store doesnt exist, create it
        if (!file_exists($tempFilePath)) {
            if (!file_exists(dirname($tempFilePath))) {
                mkdir(dirname($tempFilePath), 0777, true);
            }
        }

        // change keys back to orignal storage keys 
        $json = json_encode($properties, JSON_PRETTY_PRINT);
        $json = str_replace('"title"', '"Listing Title"', $json);
        $json = str_replace('"address"', '"Address"', $json);
        $json = str_replace('"lat"', '"Lat"', $json);
        $json = str_replace('"long"', '"Loong"', $json);
        $json = str_replace('"amenities"', '"Amenities"', $json);
        $json = str_replace('"soldBy"', '"Sold by"', $json);
        $json = str_replace('"bedrooms"', '"Bedrooms"', $json);
        $json = str_replace('"bathrooms"', '"Bathrooms"', $json);
        $json = str_replace('"receptionRooms"', '"Reception rooms"', $json);
        $json = str_replace('"image"', '"Image"', $json);
        $json = str_replace('"price"', '"Price"', $json);

        // write objs to file in json format
        file_put_contents($tempFilePath, $json);

        echo json_encode(["success" => true]);
    }

    /**
     * filter for reception, bathroom, bedroom count
     * 
     * @param string $countStr
     * @param array $jsonData
     */
    private function roomCountFilter(string $countStr, array $jsonData){
        // count array in order reception, bathroom, bedroom count
        $countArr = json_decode($countStr);
        
        return array_filter(array_map(function($property) use ($countArr) {
            if(
                $property->getReceptionRooms() == $countArr[0] && 
                $property->getBathrooms() == $countArr[1] &&
                $property->getBedrooms() == $countArr[2]
            ){
                return $property;
            } 

            return false;
        }, $jsonData));
    }

    /**
     * filter by proximity
     * 
     * @param string $postcode
     * @param array $jsonData
     */
    private function proximityFilter(string $postcode, array $jsonData){
        return array_filter(array_map(function($property) use ($postcode) {
            $postCodeLanLon = $this->getLatLongFromPostcode($postcode);

            // if distance is more than 5 miles away, add to results
            if($this->calculateDistance($postCodeLanLon['lat'], $postCodeLanLon['lon'], $property->getLat(), $property->getLong()) <= 5){
                return $property;
            } else {
                return false;
            }
        }, $jsonData));
    }

    /**
     * filter by amenities
     * 
     * @param string $amenities
     * @param array $jsonData
     */
    private function amenitiesFilter(string $amenities, array $jsonData){
        $amenitiesArr = json_decode($amenities);
        return array_filter(array_map(function($property) use ($amenitiesArr) {
            if(array_intersect($amenitiesArr, $property->getAmenities())){
                return $property;
            }

            return false;

        }, $jsonData));
    }

    /**
     * filter by price
     * 
     * @param string $priceRange
     * @param array $jsonData
     */
    private function priceFilter(string $priceRange, array $jsonData){
        $parts = explode(" - ", $priceRange);
        $fromPrice = isset($parts[0]) ? floatval($parts[0]) : null;
        $toPrice = isset($parts[1]) ? floatval($parts[1]) : null;

        return array_filter(array_map(function($property) use ($fromPrice, $toPrice) {
            $propertyPrice = floatval(str_replace(["£", ","], "", $property->getPrice()));

            // only filter by from price
            if($fromPrice && !$toPrice){
                if($propertyPrice >= $fromPrice){
                    return $property;
                }
            }
            // only filter by to price
            if($toPrice && !$fromPrice){
                if($propertyPrice <= $toPrice){
                    return $property;
                }
            }
            // filter by price range
            if($toPrice && $fromPrice){
                if($propertyPrice >= $fromPrice && $propertyPrice <= $toPrice){
                    return $property;
                }
            }
            
            return false;
        }, $jsonData));

    }

    /**
     * get lan and lon coordinates for a given UK postcode
     * 
     * @param string $postcode
     * @param bool $bulk
     * @param array $bulkData
     */
    private function getLatLongFromPostcode(string $postcode = "", bool $bulk = false, array $bulkData = []) {
        try {
            $response;
            if($bulk){
                // send post request with grouped postcodes 
                $response = json_decode(file_get_contents("https://api.postcodes.io/postcodes/{$postcode}", false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/json',
                        'content' => json_encode([
                            'postcodes' => $bulkData
                        ])
                    ]
                ])), true);
            } else {
                // send single get postcode data request
                $postcode = urlencode(str_replace(' ', '', $postcode));
                $response = json_decode(file_get_contents("https://api.postcodes.io/postcodes/{$postcode}"), true);
            }

            if($response){
                if ($response['status'] === 200){
                    if($bulk){
                        if(isset($response['result']) && count($response['result']) >= 1) {
                            // return bulk postcode data 
                            return $response['result'];
                        }
                    } else {
                        if(isset($response['result']) && isset($response['result']['latitude']) && isset($response['result']['longitude'])) {
                            // return single postcode
                            return [
                                'lat' => $response['result']['latitude'],
                                'lon' => $response['result']['longitude']
                            ];
                        }
                    }
                }
            }

            // no latitude and longitude data or response
            return null; 
        } catch (Exception $e) {
            // error gracefully
            return null;
        }
    }

    /**
     * haversine formula (miles)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        // radius of the Earth in km
        $R = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // coverting to miles for english values
        return (($R * $c) * 0.621371);
    }
}

?>
