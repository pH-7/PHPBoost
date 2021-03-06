
	# IF C_IS_SUPERADMIN #
	<div id="module-mini-sandbox" class="# IF C_SLIDE_RIGHT #mini-sbx-right# ELSE #mini-sbx-left# ENDIF #">
		<span class="sbx-toggle-btn submit# IF C_HORIZONTAL # toggle-hor# IF C_SLIDE_RIGHT # toggle-right# ELSE # toggle-left# ENDIF ## ENDIF #">
			<i class="fa fa-wrench"></i> {@module.title}
		</span>
		<div class="sbx-menu bkgd-main">
			<span class="close-btn bkgd-color-unvisible"><i class="far fa-window-close"></i> {@mini.close}</span>
			<div class="sbx-inset">
				<div class="sbx-menu-item sbx-text">
					<div class="item-2x small" title="{@mini.version.pbt}">
						{PBT_VERSION}
					</div>
					<div class="item-2x small" title="{@mini.version.php}">
						{PHP_VERSION}
					</div>
					<div class="item-2x small" title="{@mini.version.date}">
						{INSTALL_DATE}
					</div>
					<div class="item-2x small" title="{@mini.version.sql}">
						{DBMS_VERSION}
					</div>
					<div class="item-2x small" title="{@mini.viewport.h}">
						<span class="icon-stack">
			                <i class="fa fa-tv icon-main"></i>
			                <i class="fa fa-arrows-alt-h icon-sup"></i>
			            </span>
						<span class="item-number">
							<span id="window-width"></span>
						</span>
					</div>
					<div class="item-2x small" title="{@mini.viewport.v}">
						<span class="icon-stack">
			                <i class="fa fa-tv icon-main"></i>
			                <i class="fa fa-arrows-alt-v icon-sup"></i>
			            </span>
						<span class="item-number">
							<span id="window-height"></span>
						</span>
					</div>
				</div>
				<div class="sbx-menu-item">
					<div class="sbx-item-title bkgd-title">{@mini.tools}</div>
					# IF C_CSS_CACHE_ENABLED #
						<div class="item-form item-2x" title="{@mini.disable.css.cache}"># INCLUDE DISABLE_CSS_CACHE #</div>
					# ELSE #
						<div class="item-form item-2x" title="{@mini.enable.css.cache}"># INCLUDE ENABLE_CSS_CACHE #</div>
					# ENDIF #
					<div class="item-form item-2x" title="{@mini.clean.css.cache}"># INCLUDE CLEAN_CSS_CACHE #</div>
					<div class="item-form item-2x" title="{@mini.clean.tpl.cache}"># INCLUDE CLEAN_TPL_CACHE #</div>
					<div class="item-form item-2x" title="{@mini.clean.rss.cache}"># INCLUDE CLEAN_SYNDICATION_CACHE #</div>
					<div class="item-2x# IF C_LOGGED_ERRORS # blink# ENDIF #" title="{@mini.errors}">
						<a href="${relative_url(AdminErrorsUrlBuilder::logged_errors())}">
							<span class="icon-stack">
				                <i class="fa fa-terminal icon-main"></i>
				                <i class="fa fa-warning icon-sup"></i>
				            </span>
							<span class="item-number">
								({ERRORS_NB})
							</span>
						</a>
					</div>
					<div class="item-2x# IF C_404_ERRORS # blink# ENDIF #" title="{@mini.404}">
						<a href="${relative_url(AdminErrorsUrlBuilder::list_404_errors())}">
							<span class="icon-stack">
				                <i class="fa fa-unlink icon-main"></i>
				                <i class="fa fa-warning icon-sup"></i>
				            </span>
							<span class="item-number">
								({404_NB})
							</span>
						</a>
					</div>
					<div class="item-2x" title="{@mini.database}">
						<a href="{PATH_TO_ROOT}/database/admin_database.php">
							<span class="icon-stack">
				                <i class="fa fa-database icon-main"></i>
				                <i class="fa fa-cog icon-sup"></i>
				            </span>
						</a>
					</div>
					<div class="item-2x" title="{@mini.coms}">
						<a href="${relative_url(UserUrlBuilder::comments())}">
							<span class="icon-stack">
				                <i class="fa fa-comments icon-main"></i>
				                <i class="fa fa-list icon-sup"></i>
				            </span>
							<span class="item-number">
								({COMMENTS_NB})
							</span>
						</a>
					</div>
				</div>
				<div class="sbx-menu-item">
					<div class="sbx-item-title bkgd-title">{@mini.personalization}</div>
					<div class="item-3x" title="{@mini.menus}/{@mini.config}">
						<a href="{PATH_TO_ROOT}/admin/menus/menus.php">
							<span class="icon-stack">
				                <i class="fa fa-bars icon-main"></i>
				                <i class="fa fa-cog icon-sup"></i>
				            </span>
						</a>
					</div>
					<div class="item-form item-3x">
						# IF C_LEFT_ENABLED #
							<div title="{@mini.disable.left.col}"># INCLUDE DISABLE_LEFT_COL #</div>
						# ELSE #
							<div title="{@mini.enable.left.col}"># INCLUDE ENABLE_LEFT_COL #</div>
						# ENDIF #
					</div>
					<div class="item-form item-3x">
						# IF C_RIGHT_ENABLED #
							<div title="{@mini.disable.right.col}"># INCLUDE DISABLE_RIGHT_COL #</div>
						# ELSE #
							<div title="{@mini.enable.right.col}"># INCLUDE ENABLE_RIGHT_COL #</div>
						# ENDIF #
					</div>
					<div class="item-4x" title="{@mini.theme}/{@mini.manage}">
						<a href="${relative_url(AdminThemeUrlBuilder::list_installed_theme())}">
							<span class="icon-stack">
								<i class="far fa-image icon-main"></i>
								<i class="fa fa-list icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.theme}/{@mini.add}">
						<a href="${relative_url(AdminThemeUrlBuilder::add_theme())}">
							<span class="icon-stack">
								<i class="far fa-image icon-main"></i>
								<i class="fa fa-plus icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.mod}/{@mini.manage}">
						<a href="${relative_url(AdminModulesUrlBuilder::list_installed_modules())}">
							<span class="icon-stack">
								<i class="fa fa-cube icon-main"></i>
								<i class="fa fa-list icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.mod}/{@mini.add}">
						<a href="${relative_url(AdminModulesUrlBuilder::add_module())}">
							<span class="icon-stack">
								<i class="fa fa-cube icon-main"></i>
								<i class="fa fa-plus icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.user}/{@mini.manage}">
						<a href="${relative_url(AdminMembersUrlBuilder::management())}">
							<span class="icon-stack">
								<i class="far fa-user icon-main"></i>
								<i class="fa fa-list icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.user}/{@mini.add}">
						<a href="${relative_url(AdminMembersUrlBuilder::add())}">
							<span class="icon-stack">
								<i class="far fa-user icon-main"></i>
								<i class="fa fa-plus icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.config}/{@mini.general.config}">
						<a href="${relative_url(AdminConfigUrlBuilder::general_config())}">
							<span class="icon-stack">
								<i class="fa fa-university icon-main"></i>
								<i class="fa fa-cog icon-sup"></i>
							</span>
						</a>
					</div>
					<div class="item-4x" title="{@mini.config}/{@mini.advanced.config}">
						<a href="${relative_url(AdminConfigUrlBuilder::advanced_config())}">
							<span class="icon-stack">
								<i class="fa fa-university icon-main"></i>
								<i class="fa fa-cogs icon-sup"></i>
							</span>
						</a>
					</div>
				</div>
				<div class="sbx-menu-item">
					<div class="sbx-item-title bkgd-title">{@mini.sandbox.mod}</div>
					<div class="item-3x"><a href="{PATH_TO_ROOT}/sandbox/form" title="{@mini.sandbox.form}"><i class="far fa-square fa-2x"></i></a></div>
					<div class="item-3x"><a href="{PATH_TO_ROOT}/sandbox/css" title="{@mini.sandbox.css}"><i class="fab fa-css3 fa-2x"></i></a></div>
					<div class="item-3x"><a href="{PATH_TO_ROOT}/sandbox/bbcode" title="{@mini.sandbox.bbcode}"><i class="fa fa-code fa-2x"></i></a></div>
					<div class="item-3x"><a href="{PATH_TO_ROOT}/sandbox/menu" title="{@mini.sandbox.menu}"><i class="fa fa-bars fa-2x"></i></a></div>
					<div class="item-3x"><a href="{PATH_TO_ROOT}/sandbox/table" title="{@mini.sandbox.table}"><i class="fa fa-table fa-2x"></i></a></div>
					<div class="item-3x"><a href="${relative_url(SandboxUrlBuilder::config())}" title="{@mini.config}"><i class="fa fa-cogs fa-2x"></i></a></div>
				</div>
				<div class="sbx-menu-item">
					<div class="sbx-item-title bkgd-title">{@mini.themes.switcher}</div>
					<div class="item-form item-2x3">
						<form action="{REWRITED_SCRIPT}" method="get">
							<label for="switchtheme">
								<select id="switchtheme" name="switchtheme" onchange="document.location = '?switchtheme=' + this.options[this.selectedIndex].value;">
								# START themes #
									<option value="{themes.IDNAME}"# IF themes.C_SELECTED# selected="selected"# ENDIF #>{themes.NAME}</option>
								# END themes #
								</select>
							</label>
						</form>
					</div>
					<div class="item-3x">
						<a href="?switchtheme={DEFAULT_THEME}">{@mini.default.theme}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="{PATH_TO_ROOT}/sandbox/templates/js/sandbox.js"></script>
	<script src="{PATH_TO_ROOT}/kernel/lib/js/phpboost/form/validator.js"></script>
	<script src="{PATH_TO_ROOT}/kernel/lib/js/phpboost/form/form.js"></script>

# ENDIF #
