<?php
/**
 * @copyright 2012 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE.txt
 * @author Cliff Ingham <inghamn@bloomington.in.gov>
 */
$labels = Lookups::get('labels');
$labels = View::escape(implode(', ',$labels));
?>
<div class="updateLabelsForm">
	<h1>Edit Labels</h1>
	<p>
		Type the labels you want to use for issues, seperated by commas.
		These will show up on the issue editing screen and on the ticket search form.
	</p>
	<form method="post" action="<?php echo BASE_URL; ?>/lookups/updateLabels.php">
		<fieldset>
			<div><label for="labels">Labels</label></div>
			<div><textarea name="labels" id="labels"><?php echo $labels; ?></textarea></div>
			<div>
			<?php
				echo $this->template->formButton('Submit','submit','submit');
				echo $this->template->formButton(
					'Cancel','button','cancel',null,"document.location.href='".BASE_URL."/lookups/labels.php';"
				);
			?>
			</div>
		</fieldset>
	</form>
</div>