<?php
// ------------------------------------------------------------
// RapidAPI Credentials
// ------------------------------------------------------------
$RAPIDAPI_KEY  = '51c32f6f5fmshf037ccbe710e4d3p1ad0fcjsnbafd276bfade';
$RAPIDAPI_HOST = 'booking-com15.p.rapidapi.com';

// ------------------------------------------------------------
// API Endpoint with Query Parameters
// ------------------------------------------------------------
$apiUrl = "https://booking-com15.p.rapidapi.com/api/v1/hotels/searchHotels?" . http_build_query([
    'dest_id'          => -782831,           // Dubai
    'search_type'      => 'CITY',
    'arrival_date'     => '2025-12-14',
    'departure_date'   => '2025-12-20',
    'adults'           => 2,
    'children_age'     => '0,17',
    'room_qty'         => 1,
    'units'            => 'metric',
    'temperature_unit' => 'c',
    'languagecode'     => 'en-us',
    'currency_code'    => 'AED'
]);

// ------------------------------------------------------------
// cURL Initialization
// ------------------------------------------------------------
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        "X-RapidAPI-Key: {$RAPIDAPI_KEY}",
        "X-RapidAPI-Host: {$RAPIDAPI_HOST}"
    ]
]);

// ------------------------------------------------------------
// Execute API Request
// ------------------------------------------------------------
$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

// ------------------------------------------------------------
// Handle cURL Errors
// ------------------------------------------------------------
if ($curlError) {
    header("Content-Type: application/json");
    echo json_encode([
        "error" => "cURL Error: {$curlError}"
    ]);
    exit;
}

// ------------------------------------------------------------
// Decode API Response
// ------------------------------------------------------------
$apiResponse = json_decode($response, true);

// ------------------------------------------------------------
// Validate API Response
// ------------------------------------------------------------
if (
    empty($apiResponse) ||
    !isset($apiResponse['data']['hotels']) ||
    empty($apiResponse['data']['hotels'])
) {
    header("Content-Type: application/json");
    echo json_encode([
        "error" => "No hotels found or invalid API response"
    ]);
    exit;
}

// ------------------------------------------------------------
// Data Show 
// ------------------------------------------------------------
$hotelData = [];

// Total stay duration 
$totalNights = 6;
$markupPercentage = 1.15; // 15% markup

foreach ($apiResponse['data']['hotels'] as $item) {

    $hotel = $item['property'];

    // Select high-quality hotel image
    $imageUrl = '';
    if (!empty($hotel['photoUrls']) && isset($hotel['photoUrls'][1])) {
        $imageUrl = $hotel['photoUrls'][1]; // square1024 image
    }

    // Base price from API
    $actualPrice = $hotel['priceBreakdown']['grossPrice']['value'] ?? 0;

    // Per night calculation
    $pricePerNight = $totalNights > 0
        ? round($actualPrice / $totalNights, 2)
        : 0;

    // Apply markup
    $markupPrice           = round($actualPrice * $markupPercentage, 2);
    $markupPricePerNight   = round($pricePerNight * $markupPercentage, 2);

    // --------------------------------------------------------
    // All Output Show Data Like
    // --------------------------------------------------------
    $hotelData[] = (object)[
        "hotel_id" => $hotel['id'],
        "img" => $imageUrl,
        "name" => $hotel['name'] ?? 'Unknown Hotel',
        "location" => "Dubai United Arab Emirates",
        "address" => "",
        "stars" => $hotel['propertyClass'] ?? 0,
        "rating" => $hotel['reviewScore'] ?? 0,
        "latitude" => $hotel['latitude'] ?? 0,
        "longitude" => $hotel['longitude'] ?? 0,
        "actual_price" => round((float)$actualPrice, 2),
        "actual_price_per_night" => $pricePerNight,
        "markup_price" => $markupPrice,
        "markup_price_per_night" => $markupPricePerNight,
        "currency" => "USD",
        "booking_currency" => "USD",
        "service_fee" => "0",
        "supplier_name" => "hotels",
        "supplier_id" => "1",
        "redirect" => "",
        "booking_data" => (object)[],
        "color" => "#FF9900"
    ];
}

// ------------------------------------------------------------
// Output Show
// ------------------------------------------------------------
echo "<pre>";
print_r($hotelData);
echo "</pre>";
exit;

?>
