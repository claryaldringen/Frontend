
class @ArticleLoader extends CJS.Component

	constructor: (parent, id) ->
		super(parent, id)
		@offset = 4
		@blocked = no

	setMenuId: (@menuId) -> @

	run: ->
		$(document).bind 'scroll', (event) =>
			if not @blocked and document.body.scrollHeight/2 < document.body.scrollTop
				@blocked = yes
				@sendRequest('loadArticle', {menuId: @menuId, offset: @offset}, @loadArticleResponse)

	loadArticleResponse: (response) ->
		if response.length
			@offset += 4
			for article in response
				html = article
				$('#articles').append(html)
			@blocked = no
