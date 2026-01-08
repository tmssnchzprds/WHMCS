<?php if ($msg): ?>
<div class="<?php echo $msg_type; ?>box"><strong><span class="title"><?php echo $msg_type == 'error' ? $lang['error'] : $lang['done']; ?>!</span></strong><br><?php echo $msg; ?></div>
<hr />
<br />
<?php endif; ?>
<ul class="nav nav-tabs admin-tabs" role="tablist">
	<li class="active">
		<a
			class="tab-top"
			href="#icons"
			role="tab"
			id="icons-tab"
			data-toggle="tab"
			data-tab-id="1"
		><?php echo $lang['icons_menu']; ?></a>
	</li>
	<li>
		<a
			class="tab-top"
			href="#info"
			role="tab"
			id="info-tab"
			data-toggle="tab"
			data-tab-id="2"
		><?php echo $lang['info_menu']; ?></a>
	</li>
	<li class="pull-right">
		<a
			class="tab-top"
			href="#help"
			role="tab"
			id="help-tab"
			data-toggle="tab"
			data-tab-id="3"
		><?php echo $lang['help_menu']; ?></a>
	</li>
</ul>
<div class="tab-content admin-tabs">
	<div class="tab-pane active" id="icons">
		<?php if ($lang['icons_title']): ?>
		<h2><?php echo $lang['icons_title']; ?></h2>
		<?php endif; ?>
		<table class="datatable">
			<thead>
				<tr>
					<th class="text-center"><?php echo $lang['column_icon']; ?></th>
					<th class="text-center"><?php echo $lang['column_status']; ?></th>
					<th colspan="3" class="text-center"><?php echo $lang['column_actions']; ?></th>
					<th width="90%"></th>
				</tr>
			</thead>
			<tbody>
				<?php if ($files): ?>
				<?php foreach ($files as $file): ?>
					<tr>
						<td style="vertical-align:middle; text-align: center">
							<img src="<?php echo $path; ?>/modules/addons/easy_favicon/favicons/<?php echo $file; ?>" width="16" height="16" />
						</td>
						<td style="vertical-align:middle" class="text-nowrap">
							<?php if ($admin_hash && strpos($file, $admin_hash) === 0): ?>
								<span class="label" style='background-color: dodgerblue;'><?php echo $lang['status_admin']; ?></span>
							<?php endif; ?>
							<?php if ($client_hash && strpos($file, $client_hash) === 0): ?>
								<span class="label label-success"><?php echo $lang['status_client']; ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ($admin_hash && strpos($file, $admin_hash) === 0): ?>
								<a
									href="addonmodules.php?module=easy_favicon&action=unset&section=admin"
									class="btn btn-sm btn-warning"><?php echo $lang['unset_admin_button']; ?></a>
							<?php else: ?>
								<a
									href="addonmodules.php?module=easy_favicon&action=set&icon=<?php echo $file; ?>&section=admin"
									class="btn btn-sm btn-primary"><?php echo $lang['set_admin_button']; ?></a>
							<?php endif; ?>
						</td>
						<td>
							<?php if ($client_hash && strpos($file, $client_hash) === 0): ?>
								<a
									href="addonmodules.php?module=easy_favicon&action=unset&section=client"
									class="btn btn-sm btn-warning"><?php echo $lang['unset_client_button']; ?></a>
							<?php else: ?>
								<a
									href="addonmodules.php?module=easy_favicon&action=set&icon=<?php echo $file; ?>&section=client"
									class="btn btn-sm btn-primary"><?php echo $lang['set_client_button']; ?></a>
							<?php endif; ?>
						</td>
						<td>
							<a
								class="btn btn-sm btn-danger"
								href="addonmodules.php?module=easy_favicon&action=delete&icon=<?php echo $file; ?>"><?php echo $lang['del_button']; ?></a>
						</td>
						<td></td>
					</tr>
				<?php endforeach; ?>
				<?php else: ?>
				<tr><td colspan="5" class="text-center text-nowrap"><br /><?php echo $lang['no_favicons_loaded']; ?><br /><br /></td><td></tr>
				<?php endif;?>
			</tbody>
		</table>
		<br /><hr />
		<?php if ($lang['load_title']): ?>
		<h2><?php echo $lang['load_title']; ?></h2>
		<?php endif; ?>
		<form method="POST" action="addonmodules.php?module=easy_favicon&action=load" enctype="multipart/form-data">
			<span class="btn btn-sm"><input type="file" name="file" accept=".ico" /></span>
			<input type="submit" class="btn btn-sm btn-primary" value="<?php echo $lang['load_button']; ?>" />
		</form>
	</div>
	<div class="tab-pane" id="info">
		<?php if ($lang['info_title']): ?>
		<h2><?php echo $lang['info_title']; ?></h2>
		<?php endif; ?>
		<div><?php echo $lang['info_text']; ?></div>
	</div>
	<div class="tab-pane" id="help">
		<?php if ($lang['help_title']): ?>
		<h2><?php echo $lang['help_title']; ?></h2>
		<?php endif; ?>
		<div><?php echo $lang['help_text']; ?></div>
	</div>
</div>
<br /><br />
