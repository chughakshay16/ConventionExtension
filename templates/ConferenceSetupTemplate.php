<?php
/**
 * Html form for conference setup
 *
 * @file
 * @ingroup Templates
 */

/**
 * @defgroup Templates Templates
 */

if( !defined( 'MEDIAWIKI' ) ) die( -1 );

/**
 * HTML template for Special:ConferenceSetup form
 * @ingroup Templates
 */
class ConferenceSetupTemplate extends QuickTemplate
{
	function execute(){
		?>
	<div id="conferenceSetupDiv">
		<form name="conferenceSetup" method="post" action="<?php $this->text( 'action' );?>">
			<h2 class="setup"><?php $this->html( 'heading' );?></h2>
			<table>
				<tbody>
					<tr>
						<td class="mw-label">
							<label for="confTitle" ><?php $this->html( 'title' );?> : </label>
						</td>
						<td class="mw-input">
							<input type="text" id="confTitle" tabindex="1" size="25" name="titletext" /> 
						</td>
					</tr>
					<tr>
						<td class="mw-label">
							<label for="startDate"><?php $this->html( 'startdate' );?> : </label>
						</td>
						<td class="mw-input">
							<input type="text" size="7" tabindex="2" id="startDate" name="sdvalue" class="datepicker" />
						</td>
					</tr>
					<tr>
						<td class="mw-label">
							<label for="endDate"><?php $this->html( 'enddate' );?> : </label>
						</td>
						<td class="mw-input">
							<input type="text" size="7" tabindex="3" id="endDate" name="edvalue" class="datepicker" />
						</td>
					</tr>
					<tr>
						<td class="mw-label">
							<label for="capacity"><?php $this->html( 'capacity' );?> : </label>
						</td>
						<td class="mw-input">
							<input type="text" id="capacity" name="capvalue" tabindex="4" size="3" />
						</td>
					</tr>
					<tr>
						<td class="mw-label">
							<label for="countries"><?php $this->html( 'country' );?> : </label>
						</td>
						<td class="mw-input">
							<select id="countries" tabindex="5" name="country" >
								<?php foreach ($this->data['countries'] as $country){?>
								<option><?php echo $country;?></option>
								<?php }?>
							</select>
							<label for="city"><?php $this->html( 'city' );?> : </label>
							<input type="text" size="10" name="city" tabindex="6" id="city" />
							<label for="place"><?php $this->html( 'place' );?> : </label>
							<input type="text" size="15" name="place" tabindex="7" id="place" />
						</td>
					</tr>
					<tr>
						<td class="mw-label">
							<label for="description"><?php $this->html( 'description' );?> : </label>
						</td>
						<td class="mw-input">
							<textarea rows="5" cols="10" id="description" name="description" tabindex="8"></textarea>
						</td>
					</tr>
					<tr>
						<td></td>
						<td class="mw-submit">
							<input type="submit" value="<?php $this->html( 'submit' );?>" name="conferenceSetupLogin" tabindex="9" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
		<?php
	}
}