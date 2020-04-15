jQuery(document).ready(function($) {
	
	$("div[id^=qa-faq]").each(function () {
		var num = this.id.match(/qa-faq(\d+)/)[1];
		var faqContainer = $('.qa-faqs');
		var faq = $('#qa-faq' + num);
		
		if ( faqContainer.is('.collapsible') ) {

			faq.find('.qa-faq-anchor').bind("click", function() {
				if ( faqContainer.is('.accordion') ) {
					$('.qa-faq-answer').not('#qa-faq' + num + ' .qa-faq-answer').hide();
				}
				if ( faqContainer.is('.animation-fade') ) {
					faq.find('.qa-faq-answer').fadeToggle();
				} else if ( faqContainer.is('.animation-slide') ) {
					faq.find('.qa-faq-answer').slideToggle();
				} else  /* no animation */ {
					faq.find('.qa-faq-answer').toggle();
				}	

				return false;
			});
		
			$('.expand-all.expand').bind("click", function() {
				$('.expand-all.expand').hide();
				$('.expand-all.collapse').show();
				if ( faqContainer.is('.animation-fade') ) {
					$('.qa-faq-answer').fadeIn(400);
				} else if ( faqContainer.is('.animation-slide') ) {
					$('.qa-faq-answer').slideDown();
				} else  /* no animation */ {
					$('.qa-faq-answer').show();
				}	
			});

			$('.expand-all.collapse').bind("click", function() {
				$('.expand-all.collapse').hide();
				$('.expand-all.expand').show();
				if ( faqContainer.is('.animation-fade') ) {
					$('.qa-faq-answer').fadeOut(400);
				} else if ( faqContainer.is('.animation-slide') ) {
					$('.qa-faq-answer').slideUp();
				} else  /* no animation */ {
					$('.qa-faq-answer').hide();
				}	
			});
			
		}
	});

	$('.qasubmission').bind("click", function() {
		$('#postbox').fadeToggle();
	});
	
	$('#qaplus_searchform').submit(function() {
		link = $(this).find('#qa_search_link').val();
		query = $(this).find('.qaplus_search').val();
		query = query.split(' ').join('+');
		link = link + query;
		location.href = link;
		return false;
	});


});