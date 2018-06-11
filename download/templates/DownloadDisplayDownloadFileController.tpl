<section id="module-download">
	<header>
		<h1>
			<a href="{U_SYNDICATION}" title="${LangLoader::get_message('syndication', 'common')}"><i class="fa fa-syndication"></i></a>
			{@module_title}# IF NOT C_ROOT_CATEGORY # - {CATEGORY_NAME}# ENDIF # # IF IS_ADMIN #<a href="{U_EDIT_CATEGORY}" title="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit small"></i></a># ENDIF #
		</h1>
	</header>
	<div class="content">
		# IF NOT C_VISIBLE #
			# INCLUDE NOT_VISIBLE_MESSAGE #
		# ENDIF #
		<article itemscope="itemscope" itemtype="http://schema.org/CreativeWork" id="article-download-{ID}" class="article-download# IF C_NEW_CONTENT # new-content# ENDIF #">
			<header>
				<h2>
					<span id="name" itemprop="name">{NAME}</span>
					<span class="actions">
						# IF C_EDIT #
							<a href="{U_EDIT}" title="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit"></i></a>
						# ENDIF #
						# IF C_DELETE #
							<a href="{U_DELETE}" title="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete"></i></a>
						# ENDIF #
					</span>
				</h2>

				<meta itemprop="url" content="{U_LINK}">
				<meta itemprop="description" content="${escape(DESCRIPTION)}" />
				# IF C_COMMENTS_ENABLED #
				<meta itemprop="discussionUrl" content="{U_COMMENTS}">
				<meta itemprop="interactionCount" content="{NUMBER_COMMENTS} UserComments">
				# ENDIF #

			</header>
			<div class="content">
				<div class="options infos">
					<div class="center">
						# IF C_PICTURE #
							<img src="{U_PICTURE}" alt="{NAME}" itemprop="image" />
							<div class="spacer"></div>
						# ENDIF #
						# IF C_VISIBLE #
							# IF C_DISPLAY_DOWNLOAD_LINK #
								<a href="{U_DOWNLOAD}" class="basic-button">
									<i class="fa fa-download"></i> {@download}
								</a>

								# IF IS_USER_CONNECTED #
								<a href="{U_DEADLINK}" class="basic-button alt" title="${LangLoader::get_message('deadlink', 'common')}">
									<i class="fa fa-unlink"></i>
								</a>
								# ENDIF #
							# ELSE #
								# INCLUDE UNAUTHORIZED_TO_DOWNLOAD_MESSAGE #
							# ENDIF #
						# ENDIF #
					</div>
					<h6>{@file_infos}</h6>
					<span class="infos-options"><span class="text-strong">${LangLoader::get_message('size', 'common')} : </span># IF C_SIZE #{SIZE}# ELSE #${LangLoader::get_message('unknown_size', 'common')}# ENDIF #</span>
					<span class="infos-options"><span class="text-strong">${LangLoader::get_message('form.date.creation', 'common')} : </span><time datetime="# IF NOT C_DIFFERED #{DATE_ISO8601}# ELSE #{DIFFERED_START_DATE_ISO8601}# ENDIF #" itemprop="datePublished"># IF NOT C_DIFFERED #{DATE}# ELSE #{DIFFERED_START_DATE}# ENDIF #</time></span>
					# IF C_UPDATED_DATE #<span class="infos-options"><span class="text-strong">${LangLoader::get_message('form.date.update', 'common')} : </span><time datetime="{UPDATED_DATE_ISO8601}" itemprop="dateModified">{UPDATED_DATE}</time></span># ENDIF #
					<span class="infos-options"><span class="text-strong">{@downloads_number} : </span>{NUMBER_DOWNLOADS}</span>
					# IF C_NB_VIEW_ENABLED #<span class="infos-options"><span class="text-strong">{@download.number.view} : </span>{NUMBER_VIEW}</span># ENDIF #
					<span class="infos-options"><span class="text-strong">${LangLoader::get_message('category', 'categories-common')} : </span><a itemprop="about" href="{U_CATEGORY}">{CATEGORY_NAME}</a></span>
					# IF C_KEYWORDS #
						<span class="infos-options">
							<span class="text-strong">${LangLoader::get_message('form.keywords', 'common')} : </span>
							# START keywords #
								<a itemprop="keywords" href="{keywords.URL}">{keywords.NAME}</a># IF keywords.C_SEPARATOR #, # ENDIF #
							# END keywords #
						</span>
					# ENDIF #
					# IF C_AUTHOR_DISPLAYED #

						<span class="infos-options">
							<span class="text-strong">${LangLoader::get_message('author', 'common')} : </span>
							# IF C_AUTHOR_CUSTOM_NAME #
								{AUTHOR_CUSTOM_NAME}
							# ELSE #
								# IF C_AUTHOR_EXIST #<a itemprop="author" rel="author" class="{USER_LEVEL_CLASS}" href="{U_AUTHOR_PROFILE}" # IF C_USER_GROUP_COLOR # style="color:{USER_GROUP_COLOR}" # ENDIF #>{PSEUDO}</a># ELSE #{PSEUDO}# ENDIF #
							# ENDIF #
						</span>
					# ENDIF #
					# IF C_COMMENTS_ENABLED #
						<span class="infos-options"># IF C_COMMENTS # {NUMBER_COMMENTS} # ENDIF # {L_COMMENTS}</span>
					# ENDIF #
					# IF C_VISIBLE #
						# IF C_NOTATION_ENABLED #
							<div class="center">{NOTATION}</div>
						# ENDIF #
					# ENDIF #
				</div>

				<div itemprop="text">{CONTENTS}</div>
				<div class="spacer"></div>
				${ContentSharingActionsMenuService::display()}
			</div>
			<aside>
				# INCLUDE COMMENTS #
			</aside>
			<footer></footer>
		</article>
	</div>
	<footer></footer>
</section>
