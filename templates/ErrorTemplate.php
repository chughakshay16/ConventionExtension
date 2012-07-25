<?php
/**
 * Html form for showing errors
 *
 * @file
 * @ingroup Templates
 */

/**
 * @defgroup Templates Templates
 */

if( !defined( 'MEDIAWIKI' ) ) die( -1 );

/**
 * HTML template for errors in SpecialAuthorRegister.php
 * @ingroup Templates
 */
class ErrorTemplate extends QuickTemplate
{
	function execute()
	{
		?>
		<div>
			<?php if($this->haveData('errorMsg')){?>
			<p><?php $this->html('errorMsg')?></p>
			<?php }
			if($this->haveData('errorHtml') && $this->haveData('submissionsNolinkMsg')){?>
					<p><?php $this->html('submissionsNolinkMsg')?></p>
					<?php $this->data['errorHtml']?>
			<?php } elseif ($this->haveData('submissionsLinkMsg') && $this->haveData('linksto')){?>
				<p><?php $this->html('submissionsLinkMsg');?></p>
				<table>
					<tbody>
						<?php foreach ($this->data['linksto'] as $link){?>
							<tr>
								<td><a href="<?php $link['url']?>" ><?php $link['name']?></a></td>
								<td><a href="<?php $link['edit']?>" ><?php $this->html('subEdit')?></a><a href="<?php $link['delete']?>" ><?php $this->html('subDelete')?></a></td>
							</tr>
						<?php }?>	
					</tbody>
				</table>
				<p><?php $this->html('createOneMsg')?><a href="<?php $this->text('createLink')?>" ><?php $this->html('createMsg')?></a></p>
				<?php } elseif ($this->haveData('errorHtml')){
				$this->html('errorHtml');
			} else {
				$link = $this->data['linkto'];
				?>
				<table>
					<tbody>
						<tr>
							<td>
								<a href="<?php $this->haveData('userPage') ? $link['userpage'] : $links['subpage']?>"><?php $link['name']?></a>
							</td>
							<td>
								<a href="<?php $link['url']?>"><?php $this->data['deleteMsg']?></a>
							</td>
						</tr>
					</tbody>
				</table>
			<?php }?>
		</div>
		<?php 
	}
	
}