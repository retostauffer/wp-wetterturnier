/// This most outer function( $ ) preserves $ such that
/// we do not always have to use jQuery and can use this shortcut.
( function( $ ) {

    /// This is our plugin called "wetterturnier.obsPlot" providing
    /// the "obsPlot" method (jQuery) for data visualization.
    /// @args N. Integer, number of boxes which should be added.
    /// @return No return.
    $.fn.obsPlot = function( options ) {

        var globalsettings = $.extend({
            days: 4,
            color: "#556b2f",
            ajaxurl: null,
			width: 600, height: 400,
            backgroundColor: "white"
        }, options );
        $(this).globalsettings = globalsettings

        // If the user has forgotton to set N: stop. This can be done
        // using "undefined". return false exists the function.
        if ( globalsettings.statnr === null ) { alert("Wrong usage of obsPlot plugin class."); return false; }

        // Reading json data file. Uses _get_path_ as we need full paths
        // not to run into the 'Cross domain data access not allowed' thing.
        function load_data_and_create_plot(  e, settings, callback ) {

            if ( settings.ajaxurl == null ) {
                alert("Ajax url not set for $.ajax data handling routine.");
                return false;
            }

            var now = new Date().getTime(); // Prevents caching!
            var file = "json.json?" + now;
            $.ajax( {url: settings.ajaxurl, dataType: "json", type: 'post', 
                data: { action:'getobservations_ajax', d3mode:true,
                        statnr:globalsettings.statnr, days:globalsettings.days},
                // When successfully loaded the data set
                success: function( result ) {

                    var data = new Object;
                    // Array containing all data time stamps (milliseconds)
                    data.datumsec = $.map( result, function(v,k) { return v.datumsec*1000; } );
                    data.data     = result; // Store data
                    data.settings = settings; // Save station number

                    // Store/save data ranges (min/max values of all parameters)
                    data.range   = {}
                    $.each( data.data[0], function(param,v) {
                        var min=null, max=null, v=null;
                        for ( var i=0; i<result.length; i++ ) {
                            v = data.data[i][param];
                            if ( v == null )   {
                                continue;
                            } else if ( min == null ) {
                                min=v; max=v;
                            } else {
                                if ( v < min ) { min=v; }
                                if ( v > max ) { max=v; }
                            }
                        }
						// Store null if no data at all. Will be used later
						// to check for data availability.
						if ( min == null ) { data.range[param] = null; }
						else {               data.range[param] = [min,max]; }
                    });

                    // Save data onto "e", the parent object
                    e.data( data )
                    // Execute callback function ...
                    callback( e, settings );
                // On error create data dict with 'error' property for later
                // error handling.
                }, error: function() {
					// Problems with data: save error message
                    e.data( {settings:settings, error:"Problems reading data file."} );
					// Execute callback function ...
					callback( e, settings );
                }
            });
        }

        // Helper function to find closest value in an array.
        // Returns index.
        function arraySearchClosest( haystack, needle, what = "value" ) {
            var idx = null, closest = null, diff = Math.abs(haystack[0]-needle);
            $.each( haystack, function(k,v) {
                if ( idx == null || closest == null || Math.abs(v - needle) < diff)
                { idx=k; closest=v; diff = Math.abs(v-needle) }
            });
            return ( what == "value" ? closest : idx );
        }

		function create_svgs( e, globalsettings ) {
			
			// Loading data and check data
			data = $(e).data()
            // If we have encountered an error: dont plot.
			if ( data == undefined ) {
				alert("No data loaded, cannot start drawing the plot."); return false;
            } else if ( data.hasOwnProperty("error" ) ) {
                alert("Error, could not get data. Station " + data.statnr +": " + data.error); return false;
            }

			// For all plots specified:
			$.each( globalsettings.setup, function(k,options) {
				// Create new svg
				$(e).append("<div id=\"wt-obsplot-"+k+"\"></div>")
				// Adding baseplot
				draw_baseplot( e, "#wt-obsplot-"+k, globalsettings, options );
			}); 
		}

		/// @details Create the baseplot, not yet adding data.
		/// @param e. Object, parent element on which the plugin is applied.
        function draw_baseplot( e, selector, globalsettings, options ) {

        	var settings = $.extend({
				main: "N/A",
				ylab: "N/A",
                type: {},
				scalingfactor: null, // Do scaling in plot
				parameter: [], // Nothing to plot
				ylim: [null,null] // Default
        	}, options );

			// No parameters set to plot?
			if ( settings.parameter.length == 0 ) {
				$(selector).html("Setup wrong: no parameters specified for plot " + settings.main); return false;
			} else {
				var hasdata = false;
				$.each( settings.parameter, function(k,v) {
					if ( $(e).data().range[v] != null ) { hasdata=true; }
				});
				if ( ! hasdata ) {
					$(selector).html("Sorry, no data for " + settings.main + ", cannot draw plot.");
					return false;
				}
			}

            // SVG options
			// margins: outer margins
			// height:   svg height
			// width:    svg width
			// axOffset: White space top/left/bottum/right added to the axis to separate
            // 			 the data from the plotting box, in percent (4 means 4 percent from
            // 			 the whole data range to this specific side).
			var svgopt = {
                	margin:   {top: 35, right: 10, bottom: 20, left: 60},
                	width:    globalsettings.width,
                	height:   globalsettings.height,
            		axOffset: {top: 4., left:0, bottom: 4, right: 0}
				};

			// Adjust width/height, subtract defined margins
            svgopt.width  = svgopt.width  - svgopt.margin.left - svgopt.margin.right;
            svgopt.height = svgopt.height - svgopt.margin.top  - svgopt.margin.bottom;


            // Setting myself up an svg to plot to.
            var svg = d3.select(selector).append("svg")
                      .attr("width", svgopt.width  + svgopt.margin.left + svgopt.margin.right)
                      .attr("height",svgopt.height + svgopt.margin.top  + svgopt.margin.bottom)
                      .append("g").attr("transform","translate("+svgopt.margin.left+","+svgopt.margin.bottom+")")

			var yRange = [];
			$.each( settings.parameter, function( k, param ) {
				if ( ! (data.range[param] == null) ) {
					yRange = d3.extent($.merge( yRange, data.range[param] ));
				} // Else no data, ignore
			});
			yRange[0] = yRange[0] - (yRange[1]-yRange[0]) * svgopt.axOffset.top    / 100.;
			yRange[1] = yRange[1] + (yRange[1]-yRange[0]) * svgopt.axOffset.bottom / 100.;
			if ( ! (settings.scalingfactor == null) ) {
				yRange[0] = yRange[0] / parseFloat(settings.scalingfactor);
				yRange[1] = yRange[1] / parseFloat(settings.scalingfactor);
			}

			if ( ! (settings.ylim[0] === null) ) { yRange[0] = settings.ylim[0]; }
			if ( ! (settings.ylim[1] === null) ) { yRange[1] = settings.ylim[1]; }

			// Define axis
            var yRange = d3.extent( yRange ), 
                xRange = d3.extent( d3.extent(data.datumsec) );
            var yDiff = Math.abs( yRange[0]-yRange[1] ),
				xDiff = Math.abs( xRange[0]-xRange[1] );

            // Create an array where to put the vertical grid/ticks/labels
            var looper = parseInt(d3.min(xRange) / 86400000) * 86400000;
            var xTicksAt = []
            while ( looper < d3.max(xRange) ) {
                if ( looper > d3.min(xRange) ) { xTicksAt[xTicksAt.length] = looper }
                looper += 12*3600*1000;
            }

            // Setting axis scales, functions used for data transformation
            var xScale = d3.scaleLinear().range([0,svgopt.width]).domain( xRange ),
                yScale = d3.scaleLinear().range([svgopt.height,0]).domain( yRange );

			// Helper function to set proper x-ticks and labels in UTC
            function UTCTickFun( x ) {
                x = new Date( x );
                var weekday = new Array();
                weekday[0] = "Su";
                weekday[1] = "Mo";
                weekday[2] = "Tu";
                weekday[3] = "We";
                weekday[4] = "Th";
                weekday[5] = "Fr";
                weekday[6] = "Sa";
                weekday = weekday[x.getUTCDay()];
                return ((x.getUTCHours() < 10) ? "0"+x.getUTCHours() : x.getUTCHours()) + ":" +
                       ((x.getUTCMinutes() < 10) ? "0"+x.getUTCMinutes() : x.getUTCMinutes());
            }
            // Setting up the axis itself
			var yAxis = d3.axisLeft().tickSizeOuter(0).scale( yScale )
			var xAxis = d3.axisBottom().tickSizeOuter(0).scale( xScale )
                .tickFormat( UTCTickFun ).tickValues( xTicksAt );

            // Vertical grid
            svg.append("g").attr("class","x-grid").call(
                d3.axisBottom(xScale).tickSizeInner(svgopt.height).tickValues(xTicksAt).tickFormat("")
            )

            // Add mouse focus element: there is a container of class .focus which
            // is moved corresponding to the mouse position. circle and text are
            // relative to the container.
            var focus = svg.append("g").attr("class", "focus").style("display","none")
            focus.append("circle").attr("r","3px").style("fill","blue")
            focus.append("g").attr("class","box").append("text").attr("class","box").attr("x", 9).attr("dy", ".35em");
            focus.append("line").attr("class","x").attr("fill","none")
                .attr("x0",0).attr("x1",0).attr("y1",yScale(yRange[0])).attr("y2",yScale(yRange[1]));
            focus.append("line").attr("class","y").attr("fill","none")
                .attr("x0",0).attr("x1",0).attr("y1",0).attr("y2",0)

            // Plot box, make plot reactive!
            var box = svg.append("rect").attr("x",xScale(xRange[0])).attr("y",yScale(yRange[1]))
                    .attr("width", svgopt.width)
                    .attr("height",svgopt.height)
                    .attr("stroke","black")
                    .attr("stroke-width","2px")
                    .style("fill","transparent")
                    .on('mouseover', () => focus.style('display', null))
                    .on('mouseout', () => focus.style('display', 'none'))
                    .on('mousemove', mousemove);

            // Mouseover function (reactive svg)
            function mousemove() {
                const x0 = xScale.invert(d3.mouse(this)[0]);
                const y0 = yScale.invert(d3.mouse(this)[1]);
                var closest_idx = arraySearchClosest( data.datumsec, x0, "index" );
                var closest = {x: data.data[closest_idx].datumsec*1000, y: []}

				$.each(settings.parameter,function(k,param) {
					closest.y = $.merge(closest.y,[data.data[closest_idx][param]]);
				});
				closest.y = arraySearchClosest( closest.y, y0, "value" );
                closest.y = ( settings.scalingfactor == null ) ? closest.y :
                            closest.y/settings.scalingfactor;

				// Modify the plot now
                focus.select("circle").style("display","block")
                focus.attr('transform', "translate("+xScale(closest.x)+","+yScale(closest.y)+")");
                focus.select('text').text(closest.y)
                focus.select("line.x").attr("y1",svgopt.height-yScale(closest.y))
                focus.select("line.y").attr("x1",-xScale(closest.x))
            }

            // Draw axis. For x-axis: rotate labels
            var xAxis = svg.append("g").attr("class","x-axis")
                .attr("transform", "translate(0," + svgopt.height + ")").call( xAxis )
                .selectAll("text").attr("y",0).attr("x",-8).attr("dy",".35em")
                .attr("transform","rotate(-90)").style("text-anchor","end");
            var yAxis = svg.append("g")
                .attr("transform", "translate(0,0)").call( yAxis );

            // Axis label
            svg.append("text")
                .attr("text-anchor", "middle")
                .attr("transform", "translate("+ (-svgopt.margin.left/3*2) +","+(svgopt.height/2)+")rotate(-90)")
                .text( settings.ylab )
            svg.append("text")
                .attr("text-anchor", "middle")
                .attr("transform", "translate("+ (svgopt.width/2) +","+(-svgopt.margin.bottom/3)+")")
                .text( settings.main )

			// ---------------------------------------------------------------
			// Adding data in a loop
			$.each( settings.parameter, function( k, param ) {

                var plottype = ( settings.type.hasOwnProperty(param) ) ? settings.type[param] : "line";

                // Skip if no data
                if ( ! (data.range[param] == null) ) {
            	    // If plot type is line: append line
                    if ( plottype == "line" ) {
            	       var lineFunction = d3.line()
            	               .x(function(d) { return xScale(d.datumsec * 1000); })
            	               .y(function(d) { return ( settings.scalingfactor==null ) ?
                                   yScale(d[param]) : yScale(d[param]/settings.scalingfactor); });
            	       svg.append("path").attr("class","data").attr("param",param)
                               .attr("d", lineFunction(data.data))
            	               .attr("stroke", "blue").attr("stroke-width",2).attr("fill", "none");
                    } else if ( plottype == "bar" ) {

                        var width = xScale(1000.*3600) - xScale(0)
                        svg.selectAll("bar").data(data.data).enter()
                            .append("rect").attr("class","bar bar-"+param)
                            .attr("x",function(d) {return xScale(d.datumsec*1000.) - width; })
                            .attr("y",     function(d) { return yScale(d[param]);})
                            .attr("width",width)
                            .attr("height",function(d) { return yScale(0)-yScale(d[param]);})
                            .style("fill","red")
                    }
                }

			});
			// ---------------------------------------------------------------



        }

        // This is the main function which does everything.
        load_data_and_create_plot( $(this), globalsettings, create_svgs );


    };

})(jQuery);
