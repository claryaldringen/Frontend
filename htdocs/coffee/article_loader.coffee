
class @ArticleLoader extends CJS.Component

	constructor: (parent, id) ->
		super(parent, id)
		@offset = 4
		@blocked = no
		@showLink = no

	setShowLink: (@showLink) -> @

	setMenuId: (@menuId) -> @

	getFullUrl: (url) -> window.location.href.substr(0, window.location.href.length-5) + '/' + url + '.html'

	run: ->
		$(document).bind 'scroll', (event) =>
			if not @blocked and document.body.scrollHeight/2 < document.body.scrollTop
				@blocked = yes
				@sendRequest('loadArticle', {menuId: @menuId, offset: @offset}, @loadArticleResponse)

	loadArticleResponse: (response) ->
		if response.length
			@offset += 4
			for article in response
				html = article.text
				html += '<a href="' + @getFullUrl(article.url) + '">Zobrazit celý článek</a>' if @showLink
				$('#articles').append(html)
			@blocked = no
