// If you want to draw your charts with Theme colors you must run initiating charts after that current skin is loaded
$(window).bind("load", function () {

    /*Sets Themed Colors Based on Themes*/
    themeprimary = getThemeColorFromCss('themeprimary');
    themesecondary = getThemeColorFromCss('themesecondary');
    themethirdcolor = getThemeColorFromCss('themethirdcolor');
    themefourthcolor = getThemeColorFromCss('themefourthcolor');
    themefifthcolor = getThemeColorFromCss('themefifthcolor');

    //-------------------------Initiates Sparkline Chart instances in page------------------//
    InitiateSparklineCharts.init();
    
    $.ajax({
    	url: siteUrl + 'admin/ajax/applicants/statistic',
    	dataType: "json",
    	success:function( response ) {
    		document.data = response;

    		var data = [];
            console.log(response);
    		$.each( response.applicants, function( month, counter ) {
    			data.push({ 
    				y: month, 
    				a: counter.deployed, 
                    b: counter.applied, 
    				c: counter.reserved,
                    d: counter.Selected,
                    e: counter.booking
    			});
    		});

    		Morris.Bar({
                element: 'applicant-statistic',
                /*
                data: [
                  { y: '2006', a: 100, b: 90, c:80 },
                  { y: '2007', a: 75, b: 65 , c:25},
                  { y: '2008', a: 50, b: 40 , c:90},
                  { y: '2009', a: 75, b: 65 , c:15},
                  { y: '2010', a: 50, b: 40 , c:50},
                  { y: '2011', a: 75, b: 65 ,c:10},
                  { y: '2012', a: 100, b: 90 ,c:90}
                ],
                */
                data: data,
                xkey: 'y',
                ykeys: ['a', 'b', 'c', 'd', 'e'],
                labels: ['Deployed', 'Applied', 'Reserved', 'Selected', 'For Deployment'],
                hideHover: 'auto',
                barColors: [themeprimary, themesecondary, themethirdcolor, themefourthcolor, themefifthcolor]
            });

    	},
    });
});

