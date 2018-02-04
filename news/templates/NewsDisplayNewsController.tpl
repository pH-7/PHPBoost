<section id="module-news">
	<header>
		<h1>
			<a href="{U_SYNDICATION}" title="${LangLoader::get_message('syndication', 'common')}"><i class="fa fa-syndication"></i></a>
			{@news}# IF NOT C_ROOT_CATEGORY # - {CATEGORY_NAME}# ENDIF # # IF IS_ADMIN #<a href="{U_EDIT_CATEGORY}" title="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit small"></i></a># ENDIF #
		</h1>
	</header>
	<div class="elements-container">
		# IF NOT C_VISIBLE #
			<div class="content">
				# INCLUDE NOT_VISIBLE_MESSAGE #
			</div>
		# ENDIF #
		<article itemscope="itemscope" itemtype="http://schema.org/CreativeWork" id="article-news-{ID}" class="article-news# IF C_NEW_CONTENT # new-content# ENDIF #">
			<header>
				<h2>
					<span itemprop="name">{NAME}</span>
					<span class="actions">
						# IF C_EDIT #
							<a href="{U_EDIT}" title="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit"></i></a>
						# ENDIF #
						# IF C_DELETE #
							<a href="{U_DELETE}" title="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete"></i></a>
						# ENDIF #
					</span>
				</h2>

				<div class="more">
					# IF C_AUTHOR_DISPLAYED #
					<span class="more-element more-element-author">
						<i class="fa fa-user-o"></i>
						# IF C_AUTHOR_CUSTOM_NAME #
							{AUTHOR_CUSTOM_NAME}
						# ELSE #
							# IF C_AUTHOR_EXIST #<a itemprop="author" rel="author" class="{USER_LEVEL_CLASS}" href="{U_AUTHOR_PROFILE}" # IF C_USER_GROUP_COLOR # style="color:{USER_GROUP_COLOR}" # ENDIF #>{PSEUDO}</a># ELSE #{PSEUDO}# ENDIF #
						# ENDIF #
						 | 
					</span>
					# ENDIF #
					<span class="more-element more-element-date">
						<i class="fa fa-calendar"></i> <time datetime="# IF NOT C_DIFFERED #{DATE_ISO8601}# ELSE #{DIFFERED_START_DATE_ISO8601}# ENDIF #" itemprop="datePublished"># IF NOT C_DIFFERED #{DATE}# ELSE #{DIFFERED_START_DATE}# ENDIF #</time>
					</span> | 
					<span class="more-element more-element-category">
						<i class="fa fa-folder-o"></i> <a itemprop="about" href="{U_CATEGORY}">{CATEGORY_NAME}</a>
					</span>
					${ContentSharingActionsMenuService::display()}
					<!--
						<div id="more-element-social-container" class="more-element more-element-social">
						<div class="social-button">
							<a href="#" Title="Menu des liens pour les réseaux sociaux" onclick="open_submenu('more-element-social-container');return false;"><i class="fa fa-share-alt"></i></a>
						</div>
						<ul class="social-elements-list">
							# IF C_COMMENTS_ENABLED #
							<li class="social-element social-element-comments">
								<a href="#" title="{NUMBER_COMMENTS} commentaires"><i class="fa fa-comments-o"></i> {NUMBER_COMMENTS}</a>
							</li>
							# ENDIF #
							# IF C_NB_VIEW_ENABLED #
							<li class="social-element social-element-view">
								<span title="{NUMBER_VIEW} {@news.view}"><i class="fa fa-eye"></i> {NUMBER_VIEW}</span>
							</li>
							# ENDIF #
							<li class="social-element social-element-facebook">
								<a href="#" title="Partager sur Facebook" class="fb"><i class="fa fa-facebook"></i></a>
							</li>
							<li class="social-element social-element-google">
								<a href="#" title="Partager sur Google +" class="google"><i class="fa fa-google-plus"></i></a>
							</li>
							<li class="social-element social-element-twitter">
								<a href="#" title="Partager sur Twitter" class="twitter"><i class="fa fa-twitter"></i></a>
							</li>
						</ul>
					</div>
				-->
				</div>

				<meta itemprop="url" content="{U_LINK}">
				<meta itemprop="description" content="${escape(DESCRIPTION)}" />
				# IF C_COMMENTS_ENABLED #
				<meta itemprop="discussionUrl" content="{U_COMMENTS}">
				<meta itemprop="interactionCount" content="{NUMBER_COMMENTS} UserComments">
				# ENDIF #

			</header>
			<div class="content">
				# IF C_PICTURE #<img itemprop="thumbnailUrl" src="{U_PICTURE}" alt="{NAME}" title="{NAME}" class="thumbnail-item" /># ENDIF #

				<div itemprop="text">{CONTENTS}</div>
			</div>
			<aside>
				# IF C_SOURCES #
				<div id="news-sources-container">
					<span class="news-sources-title"><i class="fa fa-map-signs"></i> ${LangLoader::get_message('form.sources', 'common')}</span> :
					# START sources #
					<a itemprop="isBasedOnUrl" href="{sources.URL}" class="small news-sources-item" rel="nofollow">{sources.NAME}</a># IF sources.C_SEPARATOR #, # ENDIF #
					# END sources #
				</div>
				# ENDIF #

				# IF C_KEYWORDS #
				<div id="news-tags-container">
					<span class="news-tags-title"><i class="fa fa-tags"></i> ${LangLoader::get_message('form.keywords', 'common')}</span> :
						# START keywords #
							<a itemprop="keywords" rel="tag" href="{keywords.URL}" class="news-tags-item">{keywords.NAME}</a># IF keywords.C_SEPARATOR #, # ENDIF #
						# END keywords #
				</div>
				# ENDIF #

				# IF C_SUGGESTED_NEWS #
					<div id="news-suggested-container">
						<span class="news-suggested-title"><i class="fa fa-lightbulb-o"></i> ${LangLoader::get_message('suggestions', 'common')} :</span>
						<ul>
							# START suggested #
							<li><a href="{suggested.URL}" class="news-suggested-item">{suggested.NAME}</a></li>
							# END suggested #
						</ul>
					</div>
				# ENDIF #

				<hr class="news-separator">

				# IF C_NEWS_NAVIGATION_LINKS #
				<div class="navigation-link">
					# IF C_PREVIOUS_NEWS #
					<span class="navigation-link-previous">
						<a href="{U_PREVIOUS_NEWS}"><i class="fa fa-arrow-circle-left"></i>{PREVIOUS_NEWS}</a>
					</span>
					# ENDIF #
					# IF C_NEXT_NEWS #
					<span class="navigation-link-next">
						<a href="{U_NEXT_NEWS}">{NEXT_NEWS}<i class="fa fa-arrow-circle-right"></i></a>
					</span>
					# ENDIF #
					<div class="spacer"></div>
				</div>
				# ENDIF #

				# INCLUDE COMMENTS #
			</aside>
			<footer></footer>
		</article>
	</div>
	<footer></footer>
</section>
