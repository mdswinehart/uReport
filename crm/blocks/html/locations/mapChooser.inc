<?php
/**
 * @copyright 2016 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 */
?>
<div id="location_map"></div>
<script type="text/javascript">
	var DEFAULT_LATITUDE  = <?= DEFAULT_LATITUDE; ?>,
		DEFAULT_LONGITUDE = <?= DEFAULT_LONGITUDE; ?>;
</script>
<button type="button" id="useThisLocation" class="submit"><?= $this->_('use_location'); ?></button>
<?php
$this->template->addToAsset('scripts', GOOGLE_MAPS);
$this->template->addToAsset('scripts', BASE_URL.'/js/locations/mapChooser.js');
