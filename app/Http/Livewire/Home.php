<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Splitter_box;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Livewire;
use Livewire\LivewireManager;
use Config;

class Home extends Component
{
    public $address;
    public $geoData;
    private $baseUrl;
    public $result;
    public $geoAddress;
    //public $geoAddress2;
    public $selectedaddress;
    public $isLocated = false;
    //public $isLocated2 = false;

    public $fullname;
    public $email;
    public $phone;
    public $closestSplitterboxDistance;
    public $customerLatitude;
    public $customerLongitude;
    public $errorMessage = '';


    public function mount()
    {
        $this->geoData = $this->splitterBoxes();
    }


    /*public function convertAddress2()
    {
        $endpoint = Config::get('constants.POSITIONSTACK_API');
        $key = Config::get('constants.POSITIONSTACK_API_KEY');
        $this->baseUrl = $endpoint.$key.'&query=';

        if($this->address != "") {
            $response = Http::get($this->baseUrl.$this->address.'&format=json');

            if ($response->successful()) {
                $res = $response->json();

                if(count($res['data']) >= 1){
                    $this->isLocated = true;
                    $this->geoAddress = $res['data'];

                } else{
                    $this->result = "Please enter a valid address";
                    session()->flash('message', $this->result);
                }
 
            } else {
                $this->result = "Please enter a valid address";
                session()->flash('message', $this->result);
            }

        } else {
            $this->result = "Address input is required";
            session()->flash('message', $this->result);
        }
    }*/



    public function convertAddress()
    {
        try {
            $endpoint = Config::get('constants.LOCATIONIQ_API');
            $key = Config::get('constants.LOCATIONIQ_API_KEY');
            $this->baseUrl = $endpoint.$key.'&q=';

            $response = Http::get($this->baseUrl.$this->address.'&format=json');

            if ($response->successful()) {
                    $res = $response->json();

                    if(count($res) >= 1){
                        $this->isLocated = true;
                        $this->geoAddress = $res;
                 
                    } else{
                        //$this->convertAddress2();
                        $this->result = "Please enter a valid address";
                        session()->flash('message', $this->result);
                    }
     
            } else {
                $this->result = "Please enter a valid address";
                session()->flash('message', $this->result);
            }
            
        } catch (Exception $e) {
            $this->result = "Something went wrong. Try again!";
            session()->flash('message', $this->result);
        }
    }



    // retrieve user selected 
    public function location()
    {   
        try {
            if($this->selectedaddress != ""){
                $geoData = explode("-", $this->selectedaddress);
                $lat = $geoData[0];
                $lon = $geoData[1];

                $this->result = $this->findLocation($lat, $lon, $this->geoData);
                session()->flash('message', $this->result);

                $this->customerLatitude = $lat;
                $this->customerLongitude = $lon;
                $this->saveLog();
            } else{
                $this->result = "Please select a specific location";
                session()->flash('message', $this->result);
            }
        } catch (Exception $e) {
            $this->result = "Something went wrong. Try again!";
            session()->flash('message', $this->result);
        }
    }



    //retrieve splitter boxes
    private function splitterBoxes()
    {
        try {
            $boxes = DB::table('splitter boxes')
            ->select('longitude','latitude', 'splitter_b')
            ->get();

            if (count($boxes) <= 0) {
                return 'No record found';
            } else {
                return $boxes;
            }
        } catch (Exception $e) {
            $this->result = "Something went wrong. Try again!";
            session()->flash('message', $this->result);
        }
    }



    // Find splitter box location
    private function findLocation($lat, $long, $locations) {
        // Set a default distance of 400 meters
        $maxDistance = 0.4; // in kilometers
        $closestLocation = null;
        $closestDistance = INF;

        // Iterate over all locations and find the closest one
        foreach ($locations as $location) {
            $lat = (double)$lat;
            $lon = (double)$long;
            $lat2 = (double)$location['latitude'];
            $lon2 = (double)$location['longitude'];

            // Calculate the distance between the user's location and this location
            $distance = $this->calculateDistance($lat, $long, $lat2, $lon2);

            // Check if this location is closer than the current closest one
            if ($distance < $closestDistance && $distance <= $maxDistance) {
                $closestLocation = $location;
                $closestDistance = $distance;
            }
        }

        $this->closestSplitterboxDistance = $closestDistance 
        == INF ? 'out of bound' :  $closestDistance;

        $found = "FIBERONE BROADBAND is available in your area";
        $notFound = "COMING SOON";
        return $closestLocation ? $found : $notFound;
    }



    // Function to calculate the distance between two points using the Haversine formula
    private function calculateDistance($lat1, $long1, $lat2, $long2)
    {
        $R = 6371; // radius of the Earth in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLong = deg2rad($long2 - $long1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLong / 2) * sin($dLong / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $R * $c;

        return $distance;
    }   



    // Refresh page
    public function refresh()
    {
        return redirect(request()->header('Referer'));
    }



    // Save customer info
    public function storeContact()
    {
        $date = date('Y-m-d H:i:s');

        try {
            if($this->fullname != '' || $this->email != '' || 
                $this->phone != '')
            {
                DB::table('coverage_user')->insert([
                    'fullname' => $this->fullname,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'latitude' => $this->customerLatitude,
                    'longitude' => $this->customerLongitude,
                    'closest_splitterbox_distance' => $this->closestSplitterboxDistance,
                    'search_result' => $this->result,
                    'created_at' => $date,
                ]);

                $this->refresh();

            } else{
                $this->errorMessage = "All fields are required";
            }
            
        } catch (Exception $e) {
            $this->result = "Something went wrong. Try again!";
            session()->flash('errorMessage', $this->result);
        }
    }



    // Save all search log
    public function saveLog()
    {
        $date = date('Y-m-d H:i:s');

        try {
            DB::table('coverage_log')->insert([
                'latitude' => $this->customerLatitude,
                'longitude' => $this->customerLongitude,
                'closest_splitterbox_distance' => $this->closestSplitterboxDistance,
                'search_result' => $this->result,
                'created_at' => $date,
            ]);
        } catch (Exception $e) {
            $this->result = "Something went wrong. Try again!";
            session()->flash('message', $this->result);
        }
    }



    public function render()
    {
        return view('livewire.home');
    }
}
