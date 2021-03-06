<?php
/**
 * @copyright 2016 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 * @param string $this->location
 * @param Ticket $this->ticket   (optional)
 */
use Application\Models\AddressService;

$ticketFields  = '';
$addressFields = '';

if ($this->ticket) {
    $locationFields = ['city', 'state', 'zip', 'latitude', 'longitude'];
    foreach ($locationFields as $field) {
        $get = 'get'.ucfirst($field);
        $$field = self::escape($this->ticket->$get());
        if ($$field) {
            $ticketFields.= "<dl class=\"{$field}\"><dt>{$this->_($field)}</dt><dd>{$$field}</dd></dl>";
        }
    }

    if ($latitude && $longitude) {
        echo "
        <div id=\"location_map\"></div>
        <script type=\"text/javascript\">
            var DEFAULT_LATITUDE  = ".DEFAULT_LATITUDE.";
            var DEFAULT_LONGITUDE = ".DEFAULT_LONGITUDE.";
        </script>
        ";
        $this->template->addToAsset('scripts', GOOGLE_MAPS);
        $this->template->addToAsset('scripts', BASE_URL.'/js/tickets/map.js');
    }
}
if (defined('ADDRESS_SERVICE')) {
    $data = AddressService::getLocationData($this->location);
    if (count($data)) {
        foreach ($data as $key=>$value) {
            $value = self::escape(trim($value));
            if (array_key_exists($key, AddressService::$customFieldDescriptions)
                && $value) {
                $label = AddressService::$customFieldDescriptions[$key]['description'];
                $addressFields.= "<dl><dt>$label</dt><dd>$value</dd></dl>";
            }
        }
    }
}

echo "
<div class=\"geodata\">
    $ticketFields
    $addressFields
</div>
";
