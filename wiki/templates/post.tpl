		<script type="text/javascript">
		<!--
			var path = '{PICTURES_DATA_PATH}';
			var selected_cat = {SELECTED_CAT};
			function check_form_post(){
				
				# IF C_BBCODE_TINYMCE_MODE #
				tinyMCE.triggerSave();
				# ENDIF #
				
				if(document.getElementById('title').value == "") {
					alert("{L_ALERT_TITLE}");
					return false;
				}
				if(document.getElementById('contents').value == "") {
					alert("{L_ALERT_CONTENTS}");
					return false;
				}
				return true;
			}
		-->
		</script>

		# START preview #
		<article>					
			<header>
				<h1>{L_PREVIEWING}: {preview.TITLE}</h1>
			</header>
			<div class="content" id="preview">
				# START preview.menu #
					<div class="row3" style="width:70%">
						<div style="text-align:center;"><strong>{L_TABLE_OF_CONTENTS}</strong></div>
						{preview.menu.MENU}
					</div>
				# END preview.menu #
				<br /><br />
				{preview.CONTENTS}
			</div>
			<footer></footer>
		</article>
		# END preview #
		
		# INCLUDE message_helper #
		
		<form action="{TARGET}" method="post" onsubmit="return check_form_post();" class="fieldset_content">					
			<fieldset>
				<p>{L_REQUIRE}</p>
				<legend>{TITLE}</legend>
				# START create #
				<div class="form-element">
					<label for="title">* {L_TITLE_FIELD}</label>
					<div class="form-field"><label><input type="text" id="title" name="title" size="70" maxlength="250" value="{ARTICLE_TITLE}"></label></div>					
				</div>
				<div class="form-element">
					<label for="selected_cat">{L_CURRENT_CAT}</label>
					<div class="form-field">
						<input type="hidden" name="id_cat" id="id_cat" value="{ID_CAT}"/>
						<div id="selected_cat">{CURRENT_CAT}</div>
					</div>					
				</div>		
				<div class="form-element">
					<label>{L_CAT}</label>
					<div class="explorer inline">
						<div class="cats">
							<div class="contents">
								<ul>
									<li><a class="{CAT_0}" id="class_0" href="javascript:select_cat(0);"><span class="icon-folder"></span>{L_DO_NOT_SELECT_ANY_CAT}</a></li>
									# START create.list #
										{create.list.DIRECTORY}
									# END create.list #
									{CAT_LIST}
								</ul>
							</div>
						</div>
					</div>					
				</div>
				# END create #	
				<div class="form-element-textarea">
					<label for="contents">* {L_CONTENTS}</label>
					# INCLUDE post_js_tools #
					{KERNEL_EDITOR}
					<textarea rows="25" cols="66" id="contents" name="contents">{CONTENTS}</textarea>
				</div>
			</fieldset>	
			<fieldset class="fieldset_submit">
				<legend>{L_SUBMIT}</legend>
				<input type="hidden" name="is_cat" value="{IS_CAT}">
				<input type="hidden" name="id_edit" value="{ID_EDIT}">
				<input type="hidden" name="token" value="{TOKEN}">
				<button type="submit">{L_SUBMIT}</button>
				<button type="submit" name="preview" value="preview">{L_PREVIEW}</button>
				<button type="reset">{L_RESET}</button>
			</fieldset>
		</form>
