
class @Concert extends CJS.Component

	constructor: (@concerts) ->
		@concertsToShow = []
		@binded = no

	@getInstance: ->
		Concert.instance = new Concert() if not Concert.instance?
		Concert.instance

	setMenuId: (@menuId) -> @

	getWidth: -> document.getElementById('concert').offsetWidth

	getCount: -> Math.round(@getWidth()/320)

	load: ->
		@sendRequest('loadConcerts', {menuId: @menuId}, @loadResponse)
		@render()


	loadResponse: (response) ->
		if response.concerts?
			@concerts = response.concerts
			@concertsToShow = @concerts.slice()
			for concert,i in @concertsToShow when concert.show
				@concertsToShow.splice(0,i - Math.floor(@getCount()/2))
				break;
			$('#concert').css('visibility', 'hidden')
			@render()
		else
			window.setTimeout	=>
				@load()
			,3000
		@

	move: (@neg = no) ->
		count = @getCount()
		if @neg
			@left = 0
		else
			@left = -(@getWidth()/count)
			@render()
			document.getElementById('concert_frameset').style.left = '-' + (@getWidth()/count) + 'px'
		@interval = window.setInterval =>
			if @neg then width = (@getWidth()/count) else width = 0
			if @left < width
				@left += 10*window.devicePixelRatio
				document.getElementById('concert_frameset').style.left = (if @neg then '-' else '') + @left + 'px'
			else
				window.clearInterval(@interval)
				@render()
		, 15

	rightClick: ->
		@move(yes)
		@concertsToShow.splice(0,1)

	leftClick: ->
		index = @concerts.length*1 - @concertsToShow.length*1
		@concertsToShow.unshift(@concerts[index-1])
		@move()

	getIndex: -> @concerts.length*1 - @concertsToShow.length*1

	getHtml: ->
		html = ''
		if @concerts? and @concerts.length
			count = @getCount()
			html += '<div class="arrows">'
			opacity = ''
			arrowId = ''
			if @getIndex() < 1 then opacity = 'opacity50' else arrowId = 'concert_left'
			html += '<img id="' + arrowId + '" class="left_arrow ' + opacity + '" src="/images/arrow.png">'
			opacity = ''
			arrowId = ''
			if @getIndex() >= @concerts.length - count then opacity = 'opacity50' else arrowId = 'concert_right'
			html += '<img id="' + arrowId + '" class="right_arrow ' + opacity + '" src="/images/arrow.png">'
			html += '</div>'
			html += '<div class="concert_frame">'
			html += '<div id="concert_frameset" style="width: ' + (@getWidth()/count)*(count+1) + 'px">'
			for concert in @concertsToShow
				html += '<div class="concert" style="width: ' + ((@getWidth()/count) - 5) + 'px">'
				#html += '<img src="http://cms.freetech.cz/images/userimages/medium/' + concert.image + '.jpg">' if concert.image?
				html += '<h3>' + concert.name + '</h3>'
				html += concert.start_time + '<br>' + concert.place
				html += concert.text
				html += '<br>Vstupenky:&nbsp;<a href="' + concert.ticket_uri + '">' + concert.ticket_uri + '</a>' if concert.ticket_uri
				html += '</div>'
			html += '</div>'
			html += '</div>'
		else
			html += '<div class="loading">'
			html += '<img src="/images/ajax-loader.gif"><br>'
			html += 'Nahrávám seznam koncertů...'
			html += '</div>'
		html

	render: ->
		element = document.getElementById('concert')
		element.innerHTML = @getHtml()
		@bindEvents()
		window.setTimeout ->
			$('#concert').css('visibility', 'visible')
		, 200

	bindEvents: ->
		document.getElementById('concert_left')?.addEventListener 'click', => @leftClick()
		document.getElementById('concert_right')?.addEventListener 'click', => @rightClick()
		if not @binded
			@binded = yes
			window.addEventListener 'resize', => @render()