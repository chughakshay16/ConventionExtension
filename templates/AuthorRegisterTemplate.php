<?php
/**
 * Html form for author registration
 *
 * @file
 * @ingroup Templates
 */

/**
 * @defgroup Templates Templates
 */

if( !defined( 'MEDIAWIKI' ) ) die( -1 );

/**
 * HTML template for Special:AuthorRegister form
 * @ingroup Templates
 */
class AuthorRegisterTemplate extends QuickTemplate
{
	function execute(){
		$i = 0;/* for tab-index property*/
		?>
		<div id="authorRegisterDiv">
			<form name="registerSetup" method="post" action="<?php $this->text( 'action' );?>" >
				<?php if ( $this->haveData( 'error' ) && $this->data['error'] ) {?>
					<div class="errMsg">
						<p><?php $this->html( 'errorMsg' )?></p>
					</div>
				<?php }?>
				<?php if ( $this->data['showAuthor'] ) {?>
					<fieldset id="authorSet">
					<legend>
						<?php $this->html( 'authorLegend' );?>
					</legend>
					<table>
						<tr>
							<td class="mw-label"><label for="countries"><?php $this->html( 'country' );?> :</label></td>
							<td class="mw-input"><?php $this->html( 'countries' );?></td>
						</tr>
						<tr>
							<td class="mw-label"><label for="affiliation"><?php $this->html( 'affiliation' );?> :</label></td>
							<td class="mw-input"><input type="text" id="affiliation" name="affiliation" tabindex="2" size="25" value="<?php if ( $this->haveData( 'affiliationVal' ) ) {
								$this->html( 'affiliationVal' );
							}?>"/></td>
						</tr>
						<tr>
							<td class="mw-label"><label for="url"><?php $this->html( 'url' );?> :</label></td>
							<td class="mw-input"><input type="text" id="url" name="url" tabindex="3" size="35" value="<?php if ( $this->haveData( 'urlVal' ) ) {
								$this->html( 'urlVal' );
							}?>"/></td>
						</tr>
						<?php $i = 3; ?>
						<?php if ( $this->data['showAuthorSubmit'] ) {?>
						<tr>
							<td></td>
							<td class="mw-submit"><input type="submit" id="submission-submit" value="<?php $this->html( 'submit' );?>" tabindex="<?php echo ++$i;?>" /></td>
						</tr>
						<?php }?>
					</table>
					</fieldset>
				<?php }?>
				<?php if ( $this->data['showSubmission'] ) {?>
				<fieldset id="submissionSet">
					<legend>
						<?php $this->html( 'submissionLegend' );?>
					</legend>
					<table>
						<tbody>
							<tr>
								<td class="mw-label"><label for="title"><?php $this->html(' titlelbl ');?> :</label></td>
								<td class="mw-input"><input type="text" size="25" id="title" name="subtitle" tabindex="<?php echo ++$i;?>" value="<?php if ( $this->haveData( 'titleVal' ) ) {
									$this->html( 'titleVal' );
								}?>" /></td>
							</tr>
							<tr>
								<td class="mw-label"><label for="type"><?php $this->html( 'type' );?> :</label></td>
								<td class="mw-input"><input type="text" size="25" id="type" name="type" tabindex="<?php echo ++$i;?>" value="<?php if ( $this->haveData( 'typeVal' ) ) {
									$this->html( 'typeVal' );
								}?>" /></td>
							</tr>
							<tr>
								<td class="mw-label"><label for="track"><?php $this->html( 'track' );?> :</label></td>
								<td class="mw-input"><!-- add select element for track, just like countries set its value to the fetched value from the database --></td>
							</tr>
							<tr>
								<td class="mw-label"><label for="length"><?php $this->html( 'length' );?> :</label></td>
								<td class="mw-input">
									<input type="text" size="5" id="length" name="length" tabindex="<?php echo ++$i;?>" value="<?php if ( $this->haveData( 'lengthVal' ) ) {
										$this->html( 'lengthVal' );
									}?>" />
									<label for="length">( <?php $this->html( 'minsmessage' );?> )</label>
								</td>
							</tr>
							<tr>
								<td class="mw-label"><label for="slidesinfo"><?php $this->html( 'slidesinfo' );?> :</label></td>
								<td class="mw-input"><input type="text" size="35" id="slidesinfo" name="slidesinfo" tabindex="<?php echo ++$i;?>" value="<?php if ( $this->haveData( 'slidesinfoVal' ) ) {
									$this->html( 'slidesinfoVal' );
								}?>" /></td>
							</tr>
							<tr>
								<td class="mw-label"><label for="slotreq"><?php $this->html( 'slotreq' );?> :</label></td>
								<td class="mw-input"><input type="text" size="25" id="slotreq" name="slotreq" tabindex="<?php echo ++$i;?>" value="<?php if ( $this->haveData( 'slotreqVal' ) ) {
									$this->html( 'slotreqVal' );
								}?>" /></td>
							</tr>
							<tr>
								<td class="mw-label"><label for="abstract"><?php $this->html( 'abstract' );?> :</label></td>
								<td class="mw-input"><textarea id="abstract" name="abstract" tabindex="<?php echo ++$i;?>" rows="5" cols="30"><?php if ( $this->haveData( 'abstractVal' ) ) {
									$this->html( 'abstractVal' );
								}?></textarea></td>
							</tr>
							<tr>
								<td></td>
								<td class="mw-submit"><input type="submit" id="submission-submit" value="<?php $this->html( 'submit' );?>" tabindex="<?php echo ++$i;?>" /></td>
								<?php if ( $this->haveData( 'create' ) ) {?>
								<td><input type="hidden" name="create" value="<?php $this->html( 'create' );?>" /></td>
								<?php }?>
							</tr>
						</tbody>
					</table>
				</fieldset>
				<?php }?>
			</form>
		</div><?php
	}
}