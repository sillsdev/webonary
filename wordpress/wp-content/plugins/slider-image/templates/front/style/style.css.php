<style>
<?php if(Hugeit_Slider_Options::get_share_buttons() == 1){ ?>
.slider_<?php echo $slider_id; ?> {
    margin-bottom: 110px
}
<?php } ?>
.share_buttons_<?php echo $slider_id; ?> {
    width: auto;
    height: auto;
    margin-top: 10px
}
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle'){ ?>
.share_buttons_<?php echo $slider_id; ?> a {
    border-radius: 50% !important;
}
<?php } ?>
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && Hugeit_Slider_Options::get_share_buttons_hover_style() == '6'){ ?>
.share_buttons_<?php echo $slider_id; ?> a span {
    border-radius: 50% !important;
}
<?php } ?>
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && Hugeit_Slider_Options::get_share_buttons_hover_style() == '7'){ ?>
.share_buttons_<?php echo $slider_id; ?> a {
    border-radius: 50% !important;
}
<?php } ?>
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && Hugeit_Slider_Options::get_share_buttons_hover_style() == '9'){ ?>
.share_buttons_<?php echo $slider_id; ?> a:before {
    border-radius: 50% !important;
}
<?php } ?>
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && Hugeit_Slider_Options::get_share_buttons_hover_style() == '10'){ ?>
.share_buttons_<?php echo $slider_id; ?> a .fa {
    border-radius: 50% !important;
}
<?php } ?>
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && (Hugeit_Slider_Options::get_share_buttons_hover_style() == '11' || Hugeit_Slider_Options::get_share_buttons_hover_style() == '12')){ ?>
.social-icons_<?php echo $slider_id; ?> .fa {
    border-radius: 50% !important;
}
<?php } ?>
/*
	Share buttons hover styles
*/
<?php switch(Hugeit_Slider_Options::get_share_buttons_hover_style()){
	case '0': ?>
.icon-link_<?php echo $slider_id; ?> {
    width: 50px;
    height: 50px;
    background-color: #666;
    line-height: 50px;
    text-align: center;
    vertical-align: middle;
    display: inline-block;
    cursor: pointer;
    outline: none;
    margin: 5px;
    box-sizing: content-box;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?> .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?> .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?> .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?> .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?> .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?> .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> {
    border: 2px solid #3b5998;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> {
    border: 2px solid #00aced;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> {
    border: 2px solid #dd4b39;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> {
    border: 2px solid #b81621;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> {
    border: 2px solid #007bb6;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> {
    border: 2px solid #32506d;
}

.icon-link_<?php echo $slider_id; ?> .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa {
    color: #fff;
    line-height: 49px;
    font-size: 26px;
}

@media screen and (max-width: 768px){
    .icon-link_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 30px;
    }

    .icon-link_<?php echo $slider_id; ?> .fa {
        line-height: 31px;
        font-size: 15px;
    }
}
<?php break;
	case '1': ?>
.icon-link_<?php echo $slider_id; ?> {
    width: 50px;
    height: 50px;
    background-color: #666;
    line-height: 50px;
    text-align: center;
    vertical-align: middle;
    display: inline-block;
    cursor: pointer;
    outline: none;
    margin: 5px;
    box-sizing: content-box;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background-color: #3b5998;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?> .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background-color: #00aced;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?> .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background-color: #dd4b39;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?> .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background-color: #b81621;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?> .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background-color: #007bb6;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?> .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background-color: #32506d;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?> {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?> .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover {
    -webkit-transition: background-color 150ms ease-in-out;
    transition: background-color 150ms ease-in-out;
    height: 50px;
    line-height: 50px;
    width: 50px;
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> {
    border: 2px solid #3b5998;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> {
    border: 2px solid #00aced;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> {
    border: 2px solid #dd4b39;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> {
    border: 2px solid #b81621;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> {
    border: 2px solid #007bb6;
}

.icon-link_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> {
    border: 2px solid #32506d;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa {
    line-height: 48px;
}

.icon-link_<?php echo $slider_id; ?> .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-facebook {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}
.icon-link_<?php echo $slider_id; ?>:hover .fa-twitter {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-google-plus {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-pinterest {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-linkedin {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-tumblr {
    color: #fff !important;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa {
    color: #fff;
    line-height: 49px;
    font-size: 26px;
}

@media screen and (max-width: 768px){
    .icon-link_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 30px;
        margin: 0
    }

    .icon-link_<?php echo $slider_id; ?>:hover {
        width: 30px;
        height: 30px;
        line-height: 30px;
    }

    .icon-link_<?php echo $slider_id; ?> .fa {
        line-height: 28px;
        font-size: 20px;
    }

    .icon-link_<?php echo $slider_id; ?>:hover .fa {
        line-height: 28px;
        font-size: 20px;
    }
}
<?php break;
	case '2': ?>
.icon-link_<?php echo $slider_id; ?> {
    width: 50px;
    height: 50px;
    background-color: #666;
    line-height: 50px;
    text-align: center;
    vertical-align: middle;
    display: inline-block;
    cursor: pointer;
    outline: none;
    margin: 5px;
    box-sizing: content-box;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?> {
    background-color: #3b5998;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_facebook_<?php echo $slider_id; ?>:hover .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?> {
    background-color: #00aced;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_twitter_<?php echo $slider_id; ?>:hover .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?> {
    background-color: #dd4b39;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_gp_<?php echo $slider_id; ?>:hover .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?> {
    background-color: #b81621;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_pinterest_<?php echo $slider_id; ?>:hover .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?> {
    background-color: #007bb6;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_linkedin_<?php echo $slider_id; ?>:hover .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?> {
    background-color: #32506d;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>.fill.share_buttons_tumblr_<?php echo $slider_id; ?>:hover .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover {
    -webkit-transition: background-color 150ms ease-in-out;
    transition: background-color 150ms ease-in-out;
    height: 46px;
    line-height: 46px;
    width: 46px;
    background-color: #fff;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_facebook_<?php echo $slider_id; ?> {
    border: 2px solid #3b5998;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_twitter_<?php echo $slider_id; ?> {
    border: 2px solid #00aced;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_gp_<?php echo $slider_id; ?> {
    border: 2px solid #dd4b39;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_pinterest_<?php echo $slider_id; ?> {
    border: 2px solid #b81621;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_linkedin_<?php echo $slider_id; ?> {
    border: 2px solid #007bb6;
}

.icon-link_<?php echo $slider_id; ?>:hover.share_buttons_tumblr_<?php echo $slider_id; ?> {
    border: 2px solid #32506d;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa {
    line-height: 45px;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-facebook {
    color: #3b5998;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-twitter {
    color: #00aced;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-google-plus {
    color: #dd4b39;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-pinterest {
    color: #b81621;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-linkedin {
    color: #007bb6;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?>:hover .fa-tumblr {
    color: #32506d;
    -webkit-transition: color 150ms ease-in-out;
    transition: color 150ms ease-in-out;
}

.icon-link_<?php echo $slider_id; ?> .fa {
    color: #fff;
    line-height: 49px;
    font-size: 26px;
}

@media screen and (max-width: 768px){
    .icon-link_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 30px;
        margin: 0
    }

    .icon-link_<?php echo $slider_id; ?>:hover {
        width: 26px;
        height: 26px;
        line-height: 22px;
    }

    .icon-link_<?php echo $slider_id; ?> .fa {
        line-height: 28px;
        font-size: 20px;
    }

    .icon-link_<?php echo $slider_id; ?>:hover .fa {
        line-height: 25px;
        font-size: 20px;
    }
}
<?php break;
	case '3': ?>
.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>, .share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:before, .share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?> .fa {
    -webkit-transition: all 0.35s;
    transition: all 0.35s;
    -webkit-transition-timing-function: cubic-bezier(0.31, -0.105, 0.43, 1.59);
    transition-timing-function: cubic-bezier(0.31, -0.105, 0.43, 1.59);
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:before {
    top: 90%;
    left: -110%;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?> .fa {
    -webkit-transform: scale(0.8);
    transform: scale(0.8);
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?>:before {
    background-color: #3b5998;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> .fa {
    color: #3b5998;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?>:before {
    background-color: #3cf;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> .fa {
    color: #3cf;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?>:before {
    background-color: #dc4a38;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> .fa {
    color: #dc4a38;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?>:before {
    background-color: #b81621;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> .fa {
    color: #b81621;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?>:before {
    background-color: #007bb6;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> .fa {
    color: #007bb6;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?>:before {
    background-color: #32506d;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> .fa {
    color: #32506d;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:focus:before,
.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:hover:before {
    top: -10%;
    left: -10%;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:focus .fa,
.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:hover .fa {
    color: #fff;
    -webkit-transform: scale(1);
    transform: scale(1);
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle'){ ?>
    margin-top: -5px;
<?php } ?>

}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?> {
    display: inline-block;
    background-color: #fff;
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-radius: 28%;
    box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.1);
    opacity: 0.99;
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?>:before {
    content: '';
    width: 120%;
    height: 120%;
    position: absolute;
    -webkit-transform: rotate(45deg);
    transform: rotate(45deg);
}

.share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?> .fa {
    font-size: 27px;
    vertical-align: middle;
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?> .btn_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 30px
    }
}
<?php break;
	case '4': ?>
.share_btn_<?php echo $slider_id; ?> {
    color: #fff !important;
    background: #adadad;
    text-align: center;
    text-decoration: none;
    font-family: fontawesome;
    position: relative;
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 2.5;
    margin: 0 2px;
    -o-transition: all .5s;
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    transition: all .5s;
    -webkit-font-smoothing: antialiased;
}

.share_btn_<?php echo $slider_id; ?>:hover {
    background: #666;
}

.share_btn_<?php echo $slider_id; ?> span {
    color: #666;
    position: absolute;
    font-family: sans-serif;
    bottom: 0;
    left: -25px;
    right: -25px;
    padding: 5px 7px;
    font-size: 14px;
    border-radius: 2px;
    background: #fff;
    visibility: hidden;
    opacity: 0;
    -o-transition: all .5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    -webkit-transition: all .5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    -moz-transition: all .5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transition: all .5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    z-index: 999
}

.share_btn_<?php echo $slider_id; ?> span:before {
    content: '';
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #fff;
    position: absolute;
    bottom: -5px;
    left: 40px;
}

.share_btn_<?php echo $slider_id; ?>:hover span {
    bottom: 50px;
    visibility: visible;
    opacity: 1;
}

@media screen and (max-width: 768px){
    .share_btn_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 2
    }
}
<?php break;
case '5': ?>
.btn<?php echo $slider_id; ?> {
    clear: both;
    white-space: nowrap;
    font-size: .8em;
    display: inline-block;
    box-shadow: 0 1px 5px 0 rgba(0, 0, 0, 0.35);
    margin: 2px;
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    transition: all .5s;
    overflow: hidden
}

.btn<?php echo $slider_id; ?>:hover {
    box-shadow: 0 5px 20px 0 rgba(0, 0, 0, 0.45);
}

.btn<?php echo $slider_id; ?>:focus {
    box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.4);
}

.btn<?php echo $slider_id; ?> > span, .btn-icon_<?php echo $slider_id; ?> i {
    float: left;
    padding: 13px 25px 13px 15px;
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    transition: all .5s;
    line-height: 1em
}

.btn<?php echo $slider_id; ?> > span {
    padding: 14px 18px 15px;
    white-space: nowrap;
    color: #FFF;
    background: #b8b8b8
}

.btn<?php echo $slider_id; ?>:hover > span {
    background: #b8b8b8
}

.btn<?php echo $slider_id; ?>:focus > span {
    background: #9a9a9a
}

.btn-icon_<?php echo $slider_id; ?> > i {
    position: relative;
    width: 13px;
    text-align: center;
    font-size: 1.25em;
    color: #fff;
    background: #212121
}

.btn-icon_<?php echo $slider_id; ?> > i:after {
    content: "";
    border: 8px solid;
    border-color: transparent transparent transparent #222;
    position: absolute;
    top: 13px;
    right: -15px
}

.btn-icon_<?php echo $slider_id; ?>:hover > i, .btn-icon_<?php echo $slider_id; ?>:focus > i {
    color: #FFF
}

.share_buttons_facebook_<?php echo $slider_id; ?>:hover > i, .share_buttons_facebook_<?php echo $slider_id; ?>:focus > i {
    color: #3b5998
}

.share_buttons_facebook_<?php echo $slider_id; ?> > span {
    background: #3b5998
}

.share_buttons_twitter_<?php echo $slider_id; ?>:hover > i, .share_buttons_twitter_<?php echo $slider_id; ?>:focus > i {
    color: #55acee
}

.share_buttons_twitter_<?php echo $slider_id; ?> > span {
    background: #55acee
}

.share_buttons_gp_<?php echo $slider_id; ?>:hover > i, .share_buttons_gp_<?php echo $slider_id; ?>:focus > i {
    color: #dd4b39
}

.share_buttons_gp_<?php echo $slider_id; ?> > span {
    background: #dd4b39
}

.share_buttons_pinterest_<?php echo $slider_id; ?>:hover > i, .share_buttons_pinterest_<?php echo $slider_id; ?>:focus > i {
    color: #cb2028
}

.share_buttons_pinterest_<?php echo $slider_id; ?> > span {
    background: #cb2028
}

.share_buttons_linkedin_<?php echo $slider_id; ?>:hover > i, .share_buttons_linkedin_<?php echo $slider_id; ?>:focus > i {
    color: #007bb6
}

.share_buttons_linkedin_<?php echo $slider_id; ?> > span {
    background: #007bb6
}

.share_buttons_tumblr_<?php echo $slider_id; ?>:hover > i, .share_buttons_tumblr_<?php echo $slider_id; ?>:focus > i {
    color: #32506d
}

.share_buttons_tumblr_<?php echo $slider_id; ?> > span {
    background: #32506d
}
@media screen and (max-width: 768px){
    .btn<?php echo $slider_id; ?> > span, .btn-icon_<?php echo $slider_id; ?> i {
        padding: 6px 21px 9px 8px;
    }
    .btn<?php echo $slider_id; ?> > span {
        padding: 8px 6px 10px;
    }
    .btn-icon_<?php echo $slider_id; ?> > i::after {
        top: 7px;
    }
}
<?php break;
case '6': ?>
.social_<?php echo $slider_id; ?> {
    text-decoration: none;
    color: #333333;
    font-size: 27px;
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    position: relative;
}

.social_<?php echo $slider_id; ?> + .social_<?php echo $slider_id; ?> {
    margin-left: 1rem;
}

.social_<?php echo $slider_id; ?> .fa {
    position: relative;
    z-index: 20;
    left: 50%;
    transform: translateX(-50%);
}

.social_<?php echo $slider_id; ?> span {
    z-index: 10;
    display: block;
    text-indent: -999vw;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #333333;
    width: 0;
    height: 0;
    transition: all 0.15s cubic-bezier(0, 1.17, 0.65, 1.47);
}

.social_<?php echo $slider_id; ?>:hover .fa {
    color: #fefefe;
}

.social_<?php echo $slider_id; ?>:hover span {
    width: 100%;
    height: 100%;
    box-shadow: 0 0 5px 1px rgba(0, 0, 0, 0.15);
}

.social_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> span {
    background-color: #3b5998;
}

.social_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> span {
    background-color: #00aced;
}

.social_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> span {
    background-color: #dd4b39;
}

.social_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> span {
    background-color: #cb2028;
}

.social_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> span {
    background-color: #007bb6;
}

.social_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> span {
    background-color: #32506d;
}
@media screen and (max-width: 768px){
    .social_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 20px
    }
    .social_<?php echo $slider_id; ?> + .social_<?php echo $slider_id; ?> {
        margin-left: 0
    }
}
<?php break;
case '7': ?>
.social_icons_<?php echo $slider_id; ?> {
    margin: 0;
    padding: 0;
    font-size: 100%;
    vertical-align: baseline;
    background: transparent;
}

.share_buttons_<?php echo $slider_id; ?> a {
    margin-right: 20px;
    display: block;
    color: #fff;
    text-decoration: none;
    -moz-transition: .3s;
    -webkit-transition: .3s;
    -o-transition: .3s;
    transition: .3s;
    width: 40px;
    height: 40px;
    float: left;
}

.share_buttons_<?php echo $slider_id; ?> .fa {
    left: 50%;
    transform: translateX(-50%);
    position: relative;
    font-size: 20px;
    line-height: 2
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background: rgb(59, 89, 152);
    box-shadow: 0 0 0 0 rgba(59, 89, 152, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background: rgb(0, 172, 237);
    box-shadow: 0 0 0 0 rgba(0, 172, 237, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background: rgb(221, 75, 57);
    box-shadow: 0 0 0 0 rgba(221, 75, 57, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background: rgb(203, 32, 40);
    box-shadow: 0 0 0 0 rgba(203, 32, 40, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background: rgb(0, 123, 182);
    box-shadow: 0 0 0 0 rgba(0, 123, 182, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background: rgb(50, 80, 109);
    box-shadow: 0 0 0 0 rgba(50, 80, 109, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?> .fa {
    color: rgb(59, 89, 152);
    box-shadow: 0 0 0 0 rgba(59, 89, 152, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?> .fa {
    color: rgb(0, 172, 237);
    box-shadow: 0 0 0 0 rgba(0, 172, 237, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?> .fa {
    color: rgb(221, 75, 57);
    box-shadow: 0 0 0 0 rgba(221, 75, 57, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?> .fa {
    color: rgb(203, 32, 40);
    box-shadow: 0 0 0 0 rgba(203, 32, 40, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?> .fa {
    color: rgb(0, 123, 182);
    box-shadow: 0 0 0 0 rgba(0, 123, 182, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?> .fa {
    color: rgb(50, 80, 109);
    box-shadow: 0 0 0 0 rgba(50, 80, 109, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> a:hover .fa {
    color: #fff;
}

.share_buttons_<?php echo $slider_id; ?> a {
    transition: box-shadow .4s ease-in-out;
    -moz-transition: box-shadow .4s ease-in-out;
    -webkit-transition: box-shadow .4s ease-in-out;
    -o-transition: box-shadow .4s ease-in-out
}

.share_buttons_<?php echo $slider_id; ?> a {
    background: #fff;
    transition: .4s;
    box-shadow: 0 0 0 25px transparent
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?> a {
        width: 30px;
        height: 30px;
        margin-right: 10px
    }
    .share_buttons_<?php echo $slider_id; ?> .fa {
        line-height: 1.5
    }
}
<?php break;
case '8': ?>
.social_icons_<?php echo $slider_id; ?> {
    margin: 0;
    padding: 0;
    font-size: 100%;
    vertical-align: baseline;
    background: transparent;
}

.share_buttons_<?php echo $slider_id; ?> a {
    margin-right: 7px;
    display: block;
    color: #fff;
    text-decoration: none;
    -moz-transition: .3s;
    -webkit-transition: .3s;
    -o-transition: .3s;
    transition: .3s;
    width: 40px;
    height: 40px;
    float: left;
}

.share_buttons_<?php echo $slider_id; ?> .fa {
    left: 50%;
    transform: translateX(-50%);
    position: relative;
    font-size: 20px;
    line-height: 2
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?> {
    background: rgb(59, 89, 152);
    box-shadow: 0 0 0 0 rgba(59, 89, 152, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?> {
    background: rgb(0, 172, 237);
    box-shadow: 0 0 0 0 rgba(0, 172, 237, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?> {
    background: rgb(221, 75, 57);
    box-shadow: 0 0 0 0 rgba(221, 75, 57, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?> {
    background: rgb(203, 32, 40);
    box-shadow: 0 0 0 0 rgba(203, 32, 40, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?> {
    background: rgb(0, 123, 182);
    box-shadow: 0 0 0 0 rgba(0, 123, 182, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?> {
    background: rgb(50, 80, 109);
    box-shadow: 0 0 0 0 rgba(50, 80, 109, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(59, 89, 152);
    box-shadow: 0 0 0 0 rgba(59, 89, 152, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(0, 172, 237);
    box-shadow: 0 0 0 0 rgba(0, 172, 237, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(221, 75, 57);
    box-shadow: 0 0 0 0 rgba(221, 75, 57, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(203, 32, 40);
    box-shadow: 0 0 0 0 rgba(203, 32, 40, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(0, 123, 182);
    box-shadow: 0 0 0 0 rgba(0, 123, 182, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>:hover .fa {
    color: rgb(50, 80, 109);
    box-shadow: 0 0 0 0 rgba(50, 80, 109, 0.6)
}

.share_buttons_<?php echo $slider_id; ?> a {
    transition: box-shadow .4s ease-in-out;
    -moz-transition: box-shadow .4s ease-in-out;
    -webkit-transition: box-shadow .4s ease-in-out;
    -o-transition: box-shadow .4s ease-in-out
}

.share_buttons_<?php echo $slider_id; ?> a:hover, .share_buttons_<?php echo $slider_id; ?> a:active {
    background: #fff;
    transition: .4s;
    box-shadow: 0 0 0 25px transparent
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?> a {
        width: 30px;
        height: 30px;
    }
    .share_buttons_<?php echo $slider_id; ?> .fa {
        line-height: 1.5
    }
}
<?php break;
case '9': ?>
.share_buttons_<?php echo $slider_id; ?> {
    padding: 0;
    list-style: none;
    margin: 1em;
}
.share_buttons_<?php echo $slider_id; ?> i {
    color: #fff;
    position: absolute;
    margin-top: -33px;
    margin-left: 19px;
    transition: all 265ms ease-out;
}
.share_buttons_<?php echo $slider_id; ?> a {
    display: inline-block;
}
.share_buttons_<?php echo $slider_id; ?> a:before {
    transform: scale(1);
    -ms-transform: scale(1);
    -webkit-transform: scale(1);
    content: " ";
    width: 50px;
    height: 50px;
    display: block;
    background: linear-gradient(45deg, #ff003c, #c648c8);
    transition: all 265ms ease-out;
}
.share_buttons_<?php echo $slider_id; ?> a:hover:before {
    transform: scale(0);
    transition: all 265ms ease-in;
}
.share_buttons_<?php echo $slider_id; ?> a:hover i {
    transform: scale(2.2);
    -ms-transform: scale(2.2);
    -webkit-transform: scale(2.2);
    color: #ff003c;
    background: -webkit-linear-gradient(45deg, #ff003c, #c648c8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transition: all 265ms ease-in;
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?> a:before {
        width: 30px;
        height: 30px;
    }
    .share_buttons_<?php echo $slider_id; ?> i {
        margin-top: -22px;
        margin-left: 8.5px;
    }
    .share_buttons_<?php echo $slider_id; ?> a:hover i {
        font-size: 9px
    }
}
<?php break;
	case '10': ?>
.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa {
    background: #fff;
    color: #818181;
    cursor: pointer;
    display: block;
    font-size: 23px;
    height: 40px;
    line-height: 40px;
    position: relative;
    text-align: center;
    transition: all .2s;
    width: 40px;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa:after {
    color: #818181;
    content: attr(data-count);
    font-size: 14px;
    left: 0;
    line-height: 20px;
    position: absolute;
    text-align: center;
    top: 100%;
    width: 100%;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-facebook:hover {
    box-shadow: 0 0 15px rgba(59, 89, 152, 0.5) inset;
    color: #3b5998;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-twitter:hover {
    box-shadow: 0 0 15px rgba(0, 172, 237, 0.5) inset;
    color: #00aced;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-google-plus:hover {
    box-shadow: 0 0 15px rgba(221, 75, 57, 0.5) inset;
    color: #dd4b39;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-pinterest:hover {
    box-shadow: 0 0 15px rgba(203, 32, 39, 0.5) inset;
    color: #cb2027;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-linkedin:hover {
    box-shadow: 0 0 15px rgba(0, 123, 182, 0.5) inset;
    color: #007bb6;
}

.share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa-tumblr:hover {
    box-shadow: 0 0 15px rgba(50, 80, 109, 0.5) inset;
    color: #007bb6;
}

.social__item_<?php echo $slider_id; ?> {
    display: inline-block;
    margin: 10px;
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?> .social__item_<?php echo $slider_id; ?> .fa {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 20px
    }
    .social__item_<?php echo $slider_id; ?> {
        margin: 0
    }
}
<?php break;
case '11': ?>
.social-icons_<?php echo $slider_id; ?> .fa {
    font-size: 1.8em;
}
.social-icons_<?php echo $slider_id; ?> .fa {
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    color: #FFF;
    color: rgba(255, 255, 255, 0.8);
    -webkit-transition: all 0.3s ease-in-out;
    -moz-transition: all 0.3s ease-in-out;
    -ms-transition: all 0.3s ease-in-out;
    -o-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}
.social-icons_<?php echo $slider_id; ?> .fa:hover, .social-icons_<?php echo $slider_id; ?> .fa:active {
    color: #FFF;
    -webkit-box-shadow: 1px 1px 3px #333;
    -moz-box-shadow: 1px 1px 3px #333;
    box-shadow: 1px 1px 3px #333;
}
.social-icons_<?php echo $slider_id; ?>.icon-zoom_<?php echo $slider_id; ?> .fa:hover, .social-icons_<?php echo $slider_id; ?>.icon-zoom_<?php echo $slider_id; ?> .fa:active {
    -webkit-transform: scale(1.1);
    -moz-transform: scale(1.1);
    -ms-transform: scale(1.1);
    -o-transform: scale(1.1);
    transform: scale(1.1);
}
.social-icons_<?php echo $slider_id; ?> .fa-facebook {
    background-color: #3b5998;
}
.social-icons_<?php echo $slider_id; ?> .fa-twitter {
    background-color: #3cf;
}
.social-icons_<?php echo $slider_id; ?> .fa-google-plus {
    background-color: #dc4a38;
}
.social-icons_<?php echo $slider_id; ?> .fa-pinterest {
    background-color: #b81621;
}
.social-icons_<?php echo $slider_id; ?> .fa-linkedin {
    background-color: #007bb6;
}
.social-icons_<?php echo $slider_id; ?> .fa-tumblr {
    background-color: #32506d;
}
@media screen and (max-width: 768px){
    .social-icons_<?php echo $slider_id; ?> .fa {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 1em
    }
}
<?php break;
	case '12': ?>
.social-icons_<?php echo $slider_id; ?> .fa {
    font-size: 1.8em;
}
.social-icons_<?php echo $slider_id; ?> .fa {
    width: 50px;
    height: 50px;
    line-height: 50px;
    text-align: center;
    color: #FFF;
    color: rgba(255, 255, 255, 0.8);
    -webkit-transition: all 0.3s ease-in-out;
    -moz-transition: all 0.3s ease-in-out;
    -ms-transition: all 0.3s ease-in-out;
    -o-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}
.social-icons_<?php echo $slider_id; ?> .fa:hover, .social-icons_<?php echo $slider_id; ?> .fa:active {
    color: #FFF;
    -webkit-box-shadow: 1px 1px 3px #333;
    -moz-box-shadow: 1px 1px 3px #333;
    box-shadow: 1px 1px 3px #333;
}
.social-icons_<?php echo $slider_id; ?>.icon-rotate_<?php echo $slider_id; ?> .fa:hover, .social-icons_<?php echo $slider_id; ?>.icon-rotate_<?php echo $slider_id; ?> .fa:active {
    -webkit-transform: scale(1.1) rotate(360deg);
    -moz-transform: scale(1.1) rotate(360deg);
    -ms-transform: scale(1.1) rotate(360deg);
    -o-transform: scale(1.1) rotate(360deg);
    transform: scale(1.1) rotate(360deg);
}
.social-icons_<?php echo $slider_id; ?> .fa-facebook {
    background-color: #3b5998;
}
.social-icons_<?php echo $slider_id; ?> .fa-twitter {
    background-color: #3cf;
}
.social-icons_<?php echo $slider_id; ?> .fa-google-plus {
    background-color: #dc4a38;
}
.social-icons_<?php echo $slider_id; ?> .fa-pinterest {
    background-color: #b81621;
}
.social-icons_<?php echo $slider_id; ?> .fa-linkedin {
    background-color: #007bb6;
}
.social-icons_<?php echo $slider_id; ?> .fa-tumblr {
    background-color: #32506d;
}
@media screen and (max-width: 768px){
    .social-icons_<?php echo $slider_id; ?> .fa {
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 1em

    }
}
<?php break;
	case '13': ?>
.share_buttons_<?php echo $slider_id; ?> {
    margin: 0;
    padding: 0;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    position: relative;
}

.social-icons_<?php echo $slider_id; ?> {
    -webkit-box-flex: 1;
    -ms-flex: 1 0 auto;
    flex: 1 0 auto;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    list-style: none;
    font-size: 1.25em;
    text-align: center;
    width: 50px;
    height: 50px;
    cursor: pointer;
    -webkit-transition: background .5s ease;
    transition: background .5s ease;
}
.social-icons_<?php echo $slider_id; ?> i {
    -webkit-transition: color .5s ease;
    transition: color .5s ease;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> {
    background: #3b5998;
    color: #839ccf;
    text-shadow: 2px 2px 1px rgba(38, 57, 97, 0.9);
    border-bottom: 4px solid #263961;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background: #344e86;
    color: #fff;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> {
    background: #55acee;
    color: #c9e5fa;
    text-shadow: 2px 2px 1px rgba(22, 137, 224, 0.9);
    border-bottom: 4px solid #1689e0;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background: #3ea1ec;
    color: #fff;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> {
    background: #dd4b39;
    color: #f0aea6;
    text-shadow: 2px 2px 1px rgba(172, 45, 30, 0.9);
    border-bottom: 4px solid #ac2d1e;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background: #d73925;
    color: #fff;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> {
    background: #c8232c;
    color: #e98187;
    text-shadow: 2px 2px 1px rgba(135, 24, 30, 0.9);
    border-bottom: 4px solid #87181e;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background: #b21f27;
    color: #fff;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> {
    background: #0e76a8;
    color: #46b8f0;
    text-shadow: 2px 2px 1px rgba(8, 68, 97, 0.9);
    border-bottom: 4px solid #084461;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background: #0c6590;
    color: #fff;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> {
    background: #2c587b;
    color: #5e98ef;
    text-shadow: 2px 2px 1px rgba(8, 77, 97, 0.9);
    border-bottom: 4px solid #35516D;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background: #32506d;
    color: #fff;
}
<?php break;
	case '14': ?>
.transition-animation {
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    -ms-transition: all .5s;
    -o-transition: all .5s;
    transition: all .5s;
}
.social-icons_<?php echo $slider_id; ?> {
    display: inline-block;
    position: relative;
    z-index: 1;
    width: 50px;
    height: 50px;
    font-size: 24px;
    line-height: 52px;
    text-align: center;
    margin: 10px 10px 0 0;
}
.social-icons_<?php echo $slider_id; ?>:after {
    position: absolute;
    width: 100%;
    height: 100%;
<?php if(Hugeit_Slider_Options::get_share_buttons_style() == 'circle' && Hugeit_Slider_Options::get_share_buttons_hover_style() == '14'){ ?>
    border-radius: 50%;
<?php } ?>
    content: '';
    box-sizing: content-box;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?> {
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    -ms-transition: all .5s;
    -o-transition: all .5s;
    transition: all .5s;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>:after {
    top: -7px;
    left: -7px;
    padding: 7px;
    -webkit-transition: all .5s;
    -moz-transition: all .5s;
    -ms-transition: all .5s;
    -o-transition: all .5s;
    transition: all .5s;
    -webkit-transform: scale(0.8);
    -moz-transform: scale(0.8);
    -ms-transform: scale(0.8);
    -o-transform: scale(0.8);
    transform: scale(0.8);
    opacity: 0;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>:hover:after {
    -webkit-transform: scale(1);
    -moz-transform: scale(1);
    -ms-transform: scale(1);
    -o-transform: scale(1);
    transform: scale(1);
    opacity: 1;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?> {
    color: #3b5998;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #3b5998;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background-color: #3b5998;
    color: #ccc;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?> {
    color: #3cf;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #3cf;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background-color: #3cf;
    color: #ccc;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?> {
    color: #dc4a38;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #dc4a38;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background-color: #dc4a38;
    color: #ccc;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?> {
    color: #b81621;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #b81621;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background-color: #b81621;
    color: #ccc;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?> {
    color: #007bb6;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #007bb6;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background-color: #007bb6;
    color: #ccc;
}
.social-icons_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?> {
    color: #32506d;
    background-color: #ccc;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?>:after {
    box-shadow: 0 0 0 4px #32506d;
}
.share_buttons_<?php echo $slider_id; ?> .social-icons_<?php echo $slider_id; ?>.share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background-color: #32506d;
    color: #ccc;
}
@media screen and (max-width: 768px){
    .social-icons_<?php echo $slider_id; ?> {
        width: 30px;
        height: 30px;
        font-size: 20px;
        line-height: 29px;

    }
}
<?php break;
	case '15': ?>
@import url(https://fonts.googleapis.com/css?family=Raleway:100,200);
* {
    transition: all 30ms ease-out;
    font-family: 'Raleway';
}
.share_buttons_<?php echo $slider_id; ?> a {
    line-height: 50px;
    width: 50px;
    font-size: 27px;
    text-align: center;
    font-family: 'Raleway';
    font-weight: 100;
    color: white;
    float: left;
    margin-right: 5px;
}
.share_buttons_<?php echo $slider_id; ?> a:last-child {
    margin-right: 0;
}
.share_buttons_<?php echo $slider_id; ?>:hover a {
    background-color: gray;
    opacity: .7;
}
.share_buttons_<?php echo $slider_id; ?> a:hover {
    cursor: pointer;
    animation-name: hover-anim;
    animation-duration: 100ms;
    animation-timing-function: ease-out;
    animation-iteration-count: 2;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>:hover {
    background-color: #43609c;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_facebook_<?php echo $slider_id; ?>:hover:before {
    content: "F";
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>:hover {
    background-color: #53a7e7;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_twitter_<?php echo $slider_id; ?>:hover:before {
    content: "T";
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>:hover {
    background-color: #d95232;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_gp_<?php echo $slider_id; ?>:hover:before {
    content: "G";
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>:hover {
    background-color: #b81621;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_pinterest_<?php echo $slider_id; ?>:hover:before {
    content: "P";
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>:hover {
    background-color: #0274b3;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_linkedin_<?php echo $slider_id; ?>:hover:before {
    content: "L";
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>:hover {
    background-color: #32506d;
    opacity: 1;
}
.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>:before,
.share_buttons_<?php echo $slider_id; ?> .share_buttons_tumblr_<?php echo $slider_id; ?>:hover:before {
    content: "T";
}
@keyframes hover-anim {
    0% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
}
@media screen and (max-width: 768px){
    .share_buttons_<?php echo $slider_id; ?>  a {
        width: 30px;
        height: 30px;
        font-size: 20px;
        line-height: 30px
    }
}
<?php break;
 } ?>

/* Lightbox styles */

.lightbox_iframe_cover {position:absolute;width:100%;height:100%;z-index:999}
a.slider_lightbox > div {width: 100% !important; height: 100% !important; padding: 0 !important}
.rwd-object{border:10px solid white}
.rwd-icon{speak:none;font-style:normal;font-weight:400;font-variant:normal;text-transform:none;line-height:1;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
.rwd-arrows .rwd-next,.rwd-arrows .rwd-prev{background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-arrows .rwd-next.disabled,.rwd-arrows .rwd-prev.disabled{pointer-events:none;opacity:.5}
.rwd-toolbar{z-index:1082;left:0;position:absolute;top:0;width:100%}
@media screen and (max-width:768px){.rwd-toolbar{z-index:9999999}}
.rwd-bar .rwd-icon,.rwd-toolbar .rwd-icon{cursor:pointer;color:#999;float:right;font-size:24px;line-height:27px;text-align:center;text-decoration:none!important;outline:0;-webkit-transition:color .2s linear;-o-transition:color .2s linear;transition:color .2s linear}
.rwd-bar .rwd-icon{position:absolute;bottom:0;z-index:1081}
.rwd-icon svg{cursor:pointer}
.rwd-bar .rwd-icon:hover,.rwd-toolbar .rwd-icon:hover{color:#FFF}
.rwd-bar .rwd-icon0:hover,.rwd-toolbar .rwd-icon0:hover,.rwd-arrows .rwd-icon0:hover{color:#000}
.rwd-arrows .rwd-prev, .rwd-arrows .rwd-next, .rwd-close {width: 46px;height: 46px;background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/slightbox_arrows.png' ?>);}
.rwd-arrows.arrows_1 .rwd-next {background-position: 227px 164px;}
.rwd-arrows.arrows_1 .rwd-prev {background-position: 277px 164px;}
.rwd-close.arrows_1 {width: 25px;height: 25px;background-position: 266px 194px;background-color: #fff;border-radius: 50%;}
.rwd-arrows.arrows_2 .rwd-next {background-position: 227px 110px;}
.rwd-arrows.arrows_2 .rwd-prev {background-position: 277px 110px;}
.rwd-close.arrows_2 {width: 25px;height: 25px;background-position: 266px 194px;background-color: #fff;border-radius: 50%;}
.rwd-arrows.arrows_3 .rwd-next {background-position: 227px 63px;}
.rwd-arrows.arrows_3 .rwd-prev {background-position: 277px 63px;}
.rwd-close.arrows_3 {width: 25px;height: 25px;background-position: 217px 195px;background-color: #fff;border-radius: 50%;}
.rwd-arrows.arrows_4 .rwd-next {background-position: 90px 167px;}
.rwd-arrows.arrows_4 .rwd-prev {background-position: 131px 167px;}
.rwd-close.arrows_4 {width: 30px;height: 30px;background-position: 38px 158px;}
.rwd-arrows.arrows_5 .rwd-next {background-position: 97px 108px;}
.rwd-arrows.arrows_5 .rwd-prev {background-position: 140px 108px;}
.rwd-close.arrows_5 {width: 25px;height: 25px;background-position: 43px 100px;}
.rwd-arrows.arrows_6 .rwd-next {background-position: 95px 63px;}
.rwd-arrows.arrows_6 .rwd-prev {background-position: 139px 63px;}
.rwd-close.arrows_6 {width: 35px;height: 35px;background-position: 48px 57px;}
.barCont{background:rgba(0,0,0,.9);width:100%;height:45px;position:absolute;bottom:0;z-index:1071}
#rwd-counter{color:#999;display:inline-block;font-size:16px;padding-top:12px;vertical-align:middle}
.rwd-bar #rwd-counter{position:absolute;bottom:11px;left:50%;transform:translateX(-50%);z-index:1090}
.rwd-next,.rwd-prev,.rwd-toolbar{opacity:1;-webkit-transition:-webkit-transform .35s cubic-bezier(0,0,.25,1) 0s,opacity .35s cubic-bezier(0,0,.25,1) 0s,color .2s linear;-moz-transition:-moz-transform .35s cubic-bezier(0,0,.25,1) 0s,opacity .35s cubic-bezier(0,0,.25,1) 0s,color .2s linear;-o-transition:-o-transform .35s cubic-bezier(0,0,.25,1) 0s,opacity .35s cubic-bezier(0,0,.25,1) 0s,color .2s linear;transition:transform .35s cubic-bezier(0,0,.25,1) 0s,opacity .35s cubic-bezier(0,0,.25,1) 0s,color .2s linear}
.rwd-cont .rwd-video-cont{display:inline-block;vertical-align:middle;max-width:1140px;max-height:100%;width:100%;padding:0 5px;top:50%;transform:translateY(-50%);position:relative}
.rwd-cont .rwd-container,.rwd-cont .rwd-image{max-width:100%;max-height:100%;transform:translateY(-50%);-ms-transform: translateY(-50%);-webkit-transform: translateY(-50%);-moz-transform: translateY(-50%);-o-transform: translateY(-50%);}
.rwd-cont .rwd-video{width:100%;height:0;padding-bottom:56.25%;overflow:hidden;position:relative}
.rwd-cont .rwd-video .rwd-object{display:inline-block;position:absolute;top:0;left:0;width:100%!important;height:100%!important}
.rwd-cont .rwd-video .rwd-video-play{width:84px;height:59px;position:absolute;left:50%;top:50%;margin-left:-42px;margin-top:-30px;z-index:1080;cursor:pointer}
.rwd-cont .rwd-video-object{width:100%!important;height:100%!important;position:absolute;top:0;left:0}
.rwd-cont .rwd-has-video .rwd-video-object{visibility:hidden}
.rwd-cont .rwd-has-video.rwd-video-playing .rwd-object,.rwd-cont .rwd-has-video.rwd-video-playing .rwd-video-play{display:none}
.rwd-cont .rwd-has-video.rwd-video-playing .rwd-video-object{visibility:visible}
.rwd-autoplay-button{left:50px}
.rwd-autoplay-button > .pause_bg{display:none}
.rwd-cont .rwd-caret{border-left:10px solid transparent;border-right:10px solid transparent;border-top:10px dashed;bottom:-10px;display:inline-block;height:0;left:50%;margin-left:-5px;position:absolute;vertical-align:middle;width:0}
.rwd-cont{width:100%;height:100%;position:fixed;top:0;left:0;z-index:9999999;opacity:0;-webkit-transition:opacity .15s ease 0s;-o-transition:opacity .15s ease 0s;transition:opacity .15s ease 0s}
.rwd-cont *{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
.rwd-cont.rwd-visible{opacity:1}
.rwd-cont.rwd-support .rwd-item.rwd-current,.rwd-cont.rwd-support .rwd-item.rwd-next-slide,.rwd-cont.rwd-support .rwd-item.rwd-prev-slide{-webkit-transition-duration:inherit!important;transition-duration:inherit!important;-webkit-transition-timing-function:inherit!important;transition-timing-function:inherit!important}
.rwd-cont .rwd-container{height:100%;width:100%;position:relative;overflow:hidden;margin-left:auto;margin-right:auto;top:50%;overflow:inherit}
.rwd-cont .cont-inner{width:100%;height:100%;position:absolute;left:0;top:0;white-space:nowrap}
.rwd-cont .contInner{width:40%;height:100%;position:absolute;left:60%;white-space:nowrap;z-index: 1200;background: black}
.rwd-cont.rwd-noSupport .rwd-current,.rwd-cont.rwd-support .rwd-current,.rwd-cont.rwd-support .rwd-next-slide,.rwd-cont.rwd-support .rwd-prev-slide{display:inline-block!important}
.rwd-cont .rwd-img-wrap,.rwd-cont .rwd-item{display:inline-block;text-align:center;position:absolute;width:100%;height:100%}
.rwd-cont .rwd-img-wrap{position:absolute;padding:0 5px;left:0;right:0;top:0;bottom:0}
.rwd-cont .rwd-item.rwd-complete{background-image:none}
.rwd-cont .rwd-item.rwd-current{z-index:1060}
.rwd-cont .rwd-image{display:inline-block;vertical-align:middle;width:auto !important;height:auto !important;top:50%;position:relative}
.rwd-cont.rwd-show-after-load .rwd-item .rwd-object,.rwd-cont.rwd-show-after-load .rwd-item .rwd-video-play{opacity:0;-webkit-transition:opacity .15s ease 0s;-o-transition:opacity .15s ease 0s;transition:opacity .15s ease 0s}
.rwd-cont.rwd-show-after-load .rwd-item.rwd-complete .rwd-object,.rwd-cont.rwd-show-after-load .rwd-item.rwd-complete .rwd-video-play{opacity:1}
.rwd-overlay{position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999997;background:rgba(0,0,0,.7);opacity:0;-webkit-transition:opacity .15s ease 0s;-o-transition:opacity .15s ease 0s;transition:opacity .15s ease 0s}
.rwd-overlay.in{opacity:1}
.rwd-container .rwd-thumb-cont {position: absolute;width: 100%;z-index: 1080}
.rwd-container .rwd-thumb {padding: 10px 0;height: 100%}
.rwd-container .rwd-thumb-item {border-radius: 5px;float: left;overflow: hidden;cursor: pointer;height: 100%;margin-bottom: 5px;}
@media (min-width: 768px) {.rwd-container .rwd-thumb-item {-webkit-transition: border-color 0.25s ease;-o-transition: border-color 0.25s ease;transition: border-color 0.25s ease;}}
.rwd-container .rwd-thumb-item img {width: 100%;height: 100%;object-fit: cover;}
.rwd-container .rwd-toggle-thumb {background-color: #0D0A0A;border-radius: 2px 2px 0 0;color: #999;cursor: pointer;font-size: 24px;height: 39px;line-height: 27px; padding: 5px 0;position: absolute;left: 20px;text-align: center;top: -39px;width: 50px;}

/* Open/Close effects */
.rwd-container.open_1 {animation: unfoldIn 1s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_1 {animation: unfoldOut 1s .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes unfoldIn {0% {transform: translateY(-50%) scaleY(.002);}50% {transform: translateY(-50%) scaleY(.002);}100% {transform: translateY(-50%) scaleY(1);}}
@keyframes unfoldOut {0% {transform: translateY(-50%) scaleY(1);}50% {transform: translateY(-50%) scaleY(.002);}100% {transform: translateY(-50%) scaleY(.002);}}
.rwd-container.open_2 {animation: blowUpIn .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_2 {animation: blowUpOut .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes blowUpIn {0% {transform: translateY(-50%) scale(0);}100% {transform: translateY(-50%) scale(1);}}
@keyframes blowUpOut {0% {transform: translateY(-50%) scale(1);opacity:1;}100% {transform: translateY(-50%) scale(0);opacity:0;}}
.rwd-container.open_3 {animation: roadRunnerIn .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_3 {animation: roadRunnerOut .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes roadRunnerIn {0% {transform:translate(-1500px, -50%) skewX(50deg) scaleX(1.3);}70% {transform:translate(30px, -50%) skewX(-25deg) scaleX(.9);}100% {transform:translate(0px, -50%) skewX(0deg) scaleX(1);}}
@keyframes roadRunnerOut {0% {transform:translate(0px, -50%) skewX(0deg) scaleX(1);}30% {transform:translate(-30px, -50%) skewX(-25deg) scaleX(.9);}100% {transform:translate(1500px, -50%) skewX(50deg) scaleX(1.3);}}
.rwd-container.open_4 {animation: runnerIn .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_4 {animation: runnerOut .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes runnerIn {0% {transform:translate(-1500px, -50%);}70% {transform:translate(30px, -50%);}100% {transform:translate(0px, -50%);}}
@keyframes runnerOut {0% {transform:translate(0px, -50%);}30% {transform:translate(-30px, -50%);}100% {transform:translate(1500px, -50%);}}
.rwd-container.open_5 {animation: rotateIn .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_5 {animation: rotateOut .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@-ms-keyframes rotateIn {from {-ms-transform: translateY(-50%) rotate(0deg);} to { -ms-transform: translateY(-50%)rotate(360deg);}}
@-moz-keyframes rotateIn {from {-moz-transform: translateY(-50%) rotate(0deg);} to { -moz-transform: translateY(-50%)rotate(360deg);}}
@-webkit-keyframes rotateIn {from {-webkit-transform: translateY(-50%) rotate(0deg);} to { -webkit-transform: translateY(-50%)rotate(360deg);}}
@keyframes rotateIn {from {transform: translateY(-50%) rotate(0deg);} to { transform: translateY(-50%)rotate(360deg);}}
@-ms-keyframes rotateOut {from {-ms-transform: translateY(-50%) rotate(360deg);} to { -ms-transform: translateY(-50%)rotate(0deg);}}
@-moz-keyframes rotateOut {from {-moz-transform: translateY(-50%) rotate(360deg);} to { -moz-transform: translateY(-50%)rotate(0deg);}}
@-webkit-keyframes rotateOut {from {-webkit-transform: translateY(-50%) rotate(360deg);} to { -webkit-transform: translateY(-50%)rotate(0deg);}}
@keyframes rotateOut {from {transform: translateY(-50%) rotate(360deg);} to { transform: translateY(-50%)rotate(0deg);}}
.rwd-container.open_1_r {animation: unfold_In 1s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_1_r {animation: unfold_Out 1s .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes unfold_In {0% {transform: translateY(-50%) scaleX(.002);}50% {transform: translateY(-50%) scaleX(.002);}100% {transform: translateY(-50%) scaleX(1);}}
@keyframes unfold_Out {0% {transform: translateY(-50%) scaleX(1);}50% {transform: translateY(-50%) scaleX(.002);}100% {transform: translateY(-50%) scaleX(.002);}}
.rwd-container.open_2_r {animation: blowUp_In .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_2_r {animation: blowUp_Out .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes blowUp_In {0% {transform: translateY(-50%) scale(2);}100% {transform: translateY(-50%) scale(1);}}
@keyframes blowUp_Out {0% {transform: translateY(-50%) scale(1);opacity:1;}100% {transform: translateY(-50%) scale(2);opacity:0;}}
.rwd-container.open_3_r {animation: roadRunner_In .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_3_r {animation: roadRunner_Out .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes roadRunner_In {0% {transform:translate(1500px, -50%) skewX(50deg) scaleX(1.3);}70% {transform:translate(-30px, -50%) skewX(-25deg) scaleX(.9);}100% {transform:translate(0px, -50%) skewX(0deg) scaleX(1);}}
@keyframes roadRunner_Out {0% {transform:translate(0px, -50%) skewX(0deg) scaleX(1);}30% {transform:translate(30px, -50%) skewX(-25deg) scaleX(.9);}100% {transform:translate(-1500px, -50%) skewX(50deg) scaleX(1.3);}}
.rwd-container.open_4_r {animation: runner_In .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_4_r {animation: runner_Out .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@keyframes runner_In {0% {transform:translate(1500px, -50%);}70% {transform:translate(-30px, -50%);}100% {transform:translate(0px, -50%);}}
@keyframes runner_Out {0% {transform:translate(0px, -50%);}30% {transform:translate(30px, -50%);}100% {transform:translate(-1500px, -50%);}}
.rwd-container.open_5_r {animation: rotate_In .3s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
.rwd-container.close_5_r {animation: rotate_Out .5s cubic-bezier(0.165, 0.840, 0.440, 1.000);}
@-ms-keyframes rotate_In {from {-ms-transform: translateY(-50%) rotate(360deg);} to { -ms-transform: translateY(-50%)rotate(0deg);}}
@-moz-keyframes rotate_In {from {-moz-transform: translateY(-50%) rotate(360deg);} to { -moz-transform: translateY(-50%)rotate(0deg);}}
@-webkit-keyframes rotate_In {from {-webkit-transform: translateY(-50%) rotate(360deg);} to { -webkit-transform: translateY(-50%)rotate(0deg);}}
@keyframes rotate_In {from {transform: translateY(-50%) rotate(360deg);} to { transform: translateY(-50%)rotate(0deg);}}
@-ms-keyframes rotate_Out {from {-ms-transform: translateY(-50%) rotate(0deg);} to { -ms-transform: translateY(-50%)rotate(360deg);}}
@-moz-keyframes rotate_Out {from {-moz-transform: translateY(-50%) rotate(0deg);} to { -moz-transform: translateY(-50%)rotate(360deg);}}
@-webkit-keyframes rotate_Out {from {-webkit-transform: translateY(-50%) rotate(0deg);} to { -webkit-transform: translateY(-50%)rotate(360deg);}}
@keyframes rotate_Out {from {transform: translateY(-50%) rotate(0deg);} to { transform: translateY(-50%)rotate(360deg);}}

/* Effects */
.rwd-support.rwd-no-trans .rwd-current,.rwd-support.rwd-no-trans .rwd-next-slide,.rwd-support.rwd-no-trans .rwd-prev-slide{-webkit-transition:none 0s ease 0s!important;-moz-transition:none 0s ease 0s!important;-o-transition:none 0s ease 0s!important;transition:none 0s ease 0s!important}
.rwd-support.rwd-animation .rwd-item,.rwd-support.rwd-use .rwd-item{-webkit-backface-visibility:hidden;-moz-backface-visibility:hidden;backface-visibility:hidden}
.rwd-support.slider_effect_1 .rwd-item,.rwd-support.slider_effect_3 .rwd-item,.rwd-support.slider_effect_4 .rwd-item,.rwd-support.slider_effect_5 .rwd-item,.rwd-support.slider_effect_6 .rwd-item,.rwd-support.slider_effect_7 .rwd-item,.rwd-support.slider_effect_8 .rwd-item,.rwd-support.slider_effect_9 .rwd-item,.rwd-support.slider_effect_10 .rwd-item{opacity:0}
.rwd-support.slider_effect_1 .rwd-item.rwd-current{opacity:1}
.rwd-support.slider_effect_1 .rwd-item.rwd-current,.rwd-support.slider_effect_1 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_1 .rwd-item.rwd-prev-slide{-webkit-transition:opacity .1s ease 0s;-moz-transition:opacity .1s ease 0s;-o-transition:opacity .1s ease 0s;transition:opacity .1s ease 0s}
.rwd-support.slider_effect_2.rwd-use .rwd-item{opacity:0}
.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-prev-slide{-webkit-transform:translate3d(-100%,0,0);transform:translate3d(-100%,0,0)}
.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-next-slide{-webkit-transform:translate3d(100%,0,0);transform:translate3d(100%,0,0)}
.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-current{-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);opacity:1}
.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-current,.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-next-slide,.rwd-support.slider_effect_2.rwd-use .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_2.rwd-animation .rwd-item{opacity:0;position:absolute;left:0}
.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-prev-slide{left:-100%}
.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-next-slide{left:100%}
.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-current{left:0;opacity:1}
.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-current,.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-next-slide,.rwd-support.slider_effect_2.rwd-animation .rwd-item.rwd-prev-slide{-webkit-transition:left 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:left 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:left 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:left 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_3 .rwd-item.rwd-prev-slide{-moz-transform:scale3d(1,0,1) translate3d(-100%,0,0);-o-transform:scale3d(1,0,1) translate3d(-100%,0,0);-ms-transform:scale3d(1,0,1) translate3d(-100%,0,0);-webkit-transform:scale3d(1,0,1) translate3d(-100%,0,0);transform:scale3d(1,0,1) translate3d(-100%,0,0)}
.rwd-support.slider_effect_3 .rwd-item.rwd-next-slide{-moz-transform:scale3d(1,0,1) translate3d(100%,0,0);-o-transform:scale3d(1,0,1) translate3d(100%,0,0);-ms-transform:scale3d(1,0,1) translate3d(100%,0,0);-webkit-transform:scale3d(1,0,1) translate3d(100%,0,0);transform:scale3d(1,0,1) translate3d(100%,0,0)}
.rwd-support.slider_effect_3 .rwd-item.rwd-current{-moz-transform:scale3d(1,1,1) translate3d(0,0,0);-o-transform:scale3d(1,1,1) translate3d(0,0,0);-ms-transform:scale3d(1,1,1) translate3d(0,0,0);-webkit-transform:scale3d(1,1,1) translate3d(0,0,0);transform:scale3d(1,1,1) translate3d(0,0,0);opacity:1}
.rwd-support.slider_effect_3 .rwd-item.rwd-current,.rwd-support.slider_effect_3 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_3 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_4 .rwd-item.rwd-prev-slide{-moz-transform:rotate(-360deg);-o-transform:rotate(-360deg);-ms-transform:rotate(-360deg);-webkit-transform:rotate(-360deg);transform:rotate(-360deg)}
.rwd-support.slider_effect_4 .rwd-item.rwd-next-slide{-moz-transform:rotate(360deg);-o-transform:rotate(360deg);-ms-transform:rotate(360deg);-webkit-transform:rotate(360deg);transform:rotate(360deg)}
.rwd-support.slider_effect_4 .rwd-item.rwd-current{-moz-transform:rotate(0deg);-o-transform:rotate(0deg);-ms-transform:rotate(0deg);-webkit-transform:rotate(0deg);transform:rotate(0deg);opacity:1}
.rwd-support.slider_effect_4 .rwd-item.rwd-current,.rwd-support.slider_effect_4 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_4 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_5 .rwd-item.rwd-prev-slide{-moz-transform:rotate(360deg);-o-transform:rotate(360deg);-ms-transform:rotate(360deg);-webkit-transform:rotate(360deg);transform:rotate(360deg)}
.rwd-support.slider_effect_5 .rwd-item.rwd-next-slide{-moz-transform:rotate(-360deg);-o-transform:rotate(-360deg);-ms-transform:rotate(-360deg);-webkit-transform:rotate(-360deg);transform:rotate(-360deg)}
.rwd-support.slider_effect_5 .rwd-item.rwd-current{-moz-transform:rotate(0deg);-o-transform:rotate(0deg);-ms-transform:rotate(0deg);-webkit-transform:rotate(0deg);transform:rotate(0deg);opacity:1}
.rwd-support.slider_effect_5 .rwd-item.rwd-current,.rwd-support.slider_effect_5 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_5 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_6 .rwd-item.rwd-prev-slide{-webkit-transform:translate3d(-100%,0,0);transform:translate3d(-100%,0,0)}
.rwd-support.slider_effect_6 .rwd-item.rwd-next-slide{-moz-transform:translate3d(0,0,0) scale(.5);-o-transform:translate3d(0,0,0) scale(.5);-ms-transform:translate3d(0,0,0) scale(.5);-webkit-transform:translate3d(0,0,0) scale(.5);transform:translate3d(0,0,0) scale(.5)}
.rwd-support.slider_effect_6 .rwd-item.rwd-current{-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);opacity:1}
.rwd-support.slider_effect_6 .rwd-item.rwd-current,.rwd-support.slider_effect_6 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_6 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_7 .rwd-item.rwd-prev-slide{-moz-transform:translate3d(0,0,0) scale(.5);-o-transform:translate3d(0,0,0) scale(.5);-ms-transform:translate3d(0,0,0) scale(.5);-webkit-transform:translate3d(0,0,0) scale(.5);transform:translate3d(0,0,0) scale(.5)}
.rwd-support.slider_effect_7 .rwd-item.rwd-next-slide{-webkit-transform:translate3d(100%,0,0);transform:translate3d(100%,0,0)}
.rwd-support.slider_effect_7 .rwd-item.rwd-current{-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);opacity:1}
.rwd-support.slider_effect_7 .rwd-item.rwd-current,.rwd-support.slider_effect_7 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_7 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_8 .rwd-item.rwd-prev-slide{-webkit-transform:scale3d(1.1,1.1,1.1);transform:scale3d(1.1,1.1,1.1)}
.rwd-support.slider_effect_8 .rwd-item.rwd-next-slide{-webkit-transform:scale3d(.9,.9,.9);transform:scale3d(.9,.9,.9)}
.rwd-support.slider_effect_8 .rwd-item.rwd-current{-webkit-transform:scale3d(1,1,1);transform:scale3d(1,1,1);opacity:1}
.rwd-support.slider_effect_8 .rwd-item.rwd-current,.rwd-support.slider_effect_8 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_8 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity 1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity 1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity 1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity 1s ease 0s}
.rwd-support.slider_effect_9 .rwd-item.rwd-prev-slide{-webkit-transform:translate3d(0,-100%,0);transform:translate3d(0,-100%,0)}
.rwd-support.slider_effect_9 .rwd-item.rwd-next-slide{-webkit-transform:translate3d(0,100%,0);transform:translate3d(0,100%,0)}
.rwd-support.slider_effect_9 .rwd-item.rwd-current{-webkit-transform:translate3d(0,0,0);transform:translate3d(0,0,0);opacity:1}
.rwd-support.slider_effect_9 .rwd-item.rwd-current,.rwd-support.slider_effect_9 .rwd-item.rwd-next-slide,.rwd-support.slider_effect_9 .rwd-item.rwd-prev-slide{-webkit-transition:-webkit-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-moz-transition:-moz-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;-o-transition:-o-transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s;transition:transform 1s cubic-bezier(0,0,.25,1) 0s,opacity .1s ease 0s}
.rwd-support.slider_effect_10 .rwd-item.rwd-prev-slide {-moz-transform: scale3d(0, 0, 0) translate3d(-100%, 0, 0);-o-transform: scale3d(0, 0, 0) translate3d(-100%, 0, 0);-ms-transform: scale3d(0, 0, 0) translate3d(-100%, 0, 0);-webkit-transform: scale3d(0, 0, 0) translate3d(-100%, 0, 0);transform: scale3d(0, 0, 0) translate3d(-100%, 0, 0);}
.rwd-support.slider_effect_10 .rwd-item.rwd-next-slide {-moz-transform: scale3d(0, 0, 0) translate3d(100%, 0, 0);-o-transform: scale3d(0, 0, 0) translate3d(100%, 0, 0);-ms-transform: scale3d(0, 0, 0) translate3d(100%, 0, 0);-webkit-transform: scale3d(0, 0, 0) translate3d(100%, 0, 0);transform: scale3d(0, 0, 0) translate3d(100%, 0, 0);}
.rwd-support.slider_effect_10 .rwd-item.rwd-current {-moz-transform: scale3d(1, 1, 1) translate3d(0, 0, 0);-o-transform: scale3d(1, 1, 1) translate3d(0, 0, 0);-ms-transform: scale3d(1, 1, 1) translate3d(0, 0, 0);-webkit-transform: scale3d(1, 1, 1) translate3d(0, 0, 0);transform: scale3d(1, 1, 1) translate3d(0, 0, 0);opacity: 1;}
.rwd-support.slider_effect_10 .rwd-item.rwd-prev-slide, .rwd-support.slider_effect_10 .rwd-item.rwd-next-slide, .rwd-support.slider_effect_10 .rwd-item.rwd-current {-webkit-transition: -webkit-transform 1s cubic-bezier(0, 0, 0.25, 1) 0s, opacity 1s ease 0s;=moz-transition: -moz-transform 1s cubic-bezier(0, 0, 0.25, 1) 0s, opacity 1s ease 0s;-o-transition: -o-transform 1s cubic-bezier(0, 0, 0.25, 1) 0s, opacity 1s ease 0s;transition: transform 1s cubic-bezier(0, 0, 0.25, 1) 0s, opacity 1s ease 0s;}

/* Lightbox styles end */

<?php if($slider->get_view() == 'none'){ ?>
ul#slider_<?php echo $slider_id; ?> {
    margin: 0;
    width: 100%;
    height: 100%;
    max-width: <?php echo $slider->get_width() . 'px'; ?>;
    max-height: <?php echo $slider->get_height() . 'px'; ?>;
    overflow: visible;
    padding: 0;
}

.slider_<?php echo $slider_id; ?> {
    width: 100%;
    height: 100%;
    max-width: <?php echo $slider->get_width() + 2 * Hugeit_Slider_Options::get_slideshow_border_size() . 'px'; ?>;
    max-height: <?php if($slider->get_navigate_by() === 'thumbnail'){
		echo $slider->get_height() + Hugeit_Slider_Options::get_thumb_height() + 3 * Hugeit_Slider_Options::get_slideshow_border_size() . 'px';
	} else {
		  echo $slider->get_height() + 2 * Hugeit_Slider_Options::get_slideshow_border_size() . 'px';
	} ?>;
<?php
switch($slider->get_position()){
case 'center':
    echo 'margin: 0 auto;';
    break;
case 'left':
    echo 'left: 0;';
    break;
case 'right':
    echo 'margin-left: calc(100% - ' . $slider->get_width() . 'px);';
    break;
}
?>
}

.huge-it-wrap:after,
.huge-it-slider:after,
.huge-it-thumb-wrap:after,
.huge-it-arrows:after,
.huge-it-caption:after {
    content: ".";
    display: block;
    height: 0;
    clear: both;
    line-height: 0;
    visibility: hidden;
}

.video_cover, .playSlider, .pauseSlider, div[class*=playButton] {
    display: none !important;
}

.huge-it-thumb-wrap .video_cover {
    display: block !important;
}

iframe.huge_it_vimeo_iframe {
    height: <?php echo $slider->get_height() . 'px'; ?>;
}

div[class*=slider-loader-] {
    background: rgba(0, 0, 0, 0) url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL . '/loading/loading' . Hugeit_Slider_Options::get_loading_icon_type() . '.gif'; ?>) no-repeat center;
    height: 90px;
    overflow: hidden;
    position: absolute;
    top: <?php echo ($slider->get_height() / 2 - 45) . 'px'; ?>;;
    width: <?php echo $slider->get_width() . 'px'; ?>;;
    z-index: 3;
}

.huge-it-wrap {
    opacity: 0;
    position: relative;
    border: <?php echo Hugeit_Slider_Options::get_slideshow_border_size().'px'; ?> solid <?php echo '#'.Hugeit_Slider_Options::get_slideshow_border_color(); ?>;
    -webkit-border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
    -moz-border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
    border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
<?php if(!(Hugeit_Slider_Options::get_navigation_type() === '16' && $slider->get_navigate_by() !== 'thumbnail')){
    echo 'overflow: hidden;';
}?>;
}

.huge-it-slide-bg {
    background: <?php
					list($r,$g,$b) = array_map('hexdec',str_split(Hugeit_Slider_Options::get_slider_background_color(),2));
						$titleopacity = Hugeit_Slider_Options::get_slider_background_color_transparency();
						echo 'rgba('.$r.','.$g.','.$b.','.$titleopacity.')'; ?>;
<?php if($slider->get_navigate_by() !== 'thumbnail'){
	echo 'height: 100%';
}
if($slider->get_navigate_by() == 'thumbnail'){ ?> border-bottom: <?php echo Hugeit_Slider_Options::get_slideshow_border_size().'px'; ?> solid <?php echo '#'.Hugeit_Slider_Options::get_slideshow_border_color(); ?>;
<?php } ?>
}

.huge-it-caption {
    position: absolute;
    display: block;
}

.huge-it-caption div {
    padding: 10px 20px;
    line-height: normal;
}

.slider-title {
<?php if(Hugeit_Slider_Options::get_title_has_margin() === 1){
	$width = 'calc(' . Hugeit_Slider_Options::get_title_width() . '% - 20px)';
	$margin = '10px';
} else {
	$width = Hugeit_Slider_Options::get_title_width(). '%';
	$margin = '0';
} ?> width: <?php echo $width; ?>;
    margin: <?php echo $margin;?>;
    font-size: <?php echo Hugeit_Slider_Options::get_title_font_size() . 'px'; ?>;
    color: <?php echo '#' . Hugeit_Slider_Options::get_title_color(); ?>;
    text-align: <?php echo Hugeit_Slider_Options::get_title_text_align(); ?>;
    background: <?php
					list($r,$g,$b) = array_map('hexdec',str_split(Hugeit_Slider_Options::get_title_background_color(),2));
						$titleopacity = Hugeit_Slider_Options::get_title_background_transparency();
						echo 'rgba('.$r.','.$g.','.$b.','.$titleopacity.')'; ?>;
    border: <?php echo Hugeit_Slider_Options::get_title_border_size() . 'px solid #' . Hugeit_Slider_Options::get_title_border_color(); ?>;
    border-radius: <?php echo Hugeit_Slider_Options::get_title_border_radius() . 'px'; ?>;
<?php switch(Hugeit_Slider_Options::get_title_position()){
		case '11':
			echo 'left: 0 !important; bottom: 0;';
			break;
		case '21':
			echo 'left: 50% !important; transform: translateX(-50%); bottom: 0;';
			break;
		case '31':
			echo 'right: 0 !important; bottom: 0;';
			break;
		case '12':
			echo 'left: 0 !important; top: 50%; transform: translateY(-50%);';
			break;
		case '22':
			echo 'left: 50% !important; top: 50%; transform: translate(-50%, -50%);';
			break;
		case '32':
			echo 'right: 0 !important; top: 50%; transform: translateY(-50%);';
			break;
		case '13':
			echo 'left: 0 !important; top: 0;';
			break;
		case '23':
			echo 'left: 50% !important; transform: translateX(-50%); top: 0;';
			break;
		case '33':
			echo 'right: 0 !important; top: 0;';
			break;
} ?>
}

.slider-description {
<?php if(Hugeit_Slider_Options::get_description_has_margin() === 1){
	$width = 'calc(' . Hugeit_Slider_Options::get_description_width() . '% - 20px)';
	$margin = '10px';
} else {
	$width = Hugeit_Slider_Options::get_description_width(). '%';
	$margin = '0';
} ?> width: <?php echo $width; ?>;
    margin: <?php echo $margin;?>;
    font-size: <?php echo Hugeit_Slider_Options::get_description_font_size() . 'px'; ?>;
    color: <?php echo '#' . Hugeit_Slider_Options::get_description_color(); ?>;
    text-align: <?php echo Hugeit_Slider_Options::get_description_text_align(); ?>;
    background: <?php
					list($r,$g,$b) = array_map('hexdec',str_split(Hugeit_Slider_Options::get_description_background_color(),2));
						$descriptionopacity = Hugeit_Slider_Options::get_description_background_transparency();
						echo 'rgba('.$r.','.$g.','.$b.','.$descriptionopacity.')'; ?>;

    border: <?php echo Hugeit_Slider_Options::get_description_border_size() . 'px solid #' . Hugeit_Slider_Options::get_description_border_color(); ?>;
    border-radius: <?php echo Hugeit_Slider_Options::get_description_border_radius() . 'px'; ?>;
<?php switch(Hugeit_Slider_Options::get_description_position()){
		case '11':
			echo 'left: 0 !important; bottom: 0;';
			break;
		case '21':
			echo 'left: 50% !important; transform: translateX(-50%); bottom: 0;';
			break;
		case '31':
			echo 'right: 0 !important; bottom: 0;';
			break;
		case '12':
			echo 'left: 0 !important; top: 50%; transform: translateY(-50%);';
			break;
		case '22':
			echo 'left: 50% !important; top: 50%; transform: translate(-50%, -50%);';
			break;
		case '32':
			echo 'right: 0 !important; top: 50%; transform: translateY(-50%);';
			break;
		case '13':
			echo 'left: 0 !important; top: 0;';
			break;
		case '23':
			echo 'left: 50% !important; transform: translateX(-50%); top: 0;';
			break;
		case '33':
			echo 'right: 0 !important; top: 0;';
			break;
} ?>
}

.slider_<?php echo $slider_id; ?> .huge-it-slider > li {
    list-style: none;
    filter: alpha(opacity=0);
    opacity: 0;
    width: 100%;
    height: 100%;
    margin: 0 -100% 0 0;
    padding: 0;
    float: left;
    position: relative;
<?php if(Hugeit_Slider_Options::get_crop_image() === 'fill'){
    echo 'height:  ' . $slider->get_height() . 'px;';
} ?>;
    overflow: hidden;
}

.slider_<?php echo $slider_id; ?> .huge-it-slider > li > a {
    display: block;
    padding: 0;
    background: none;
    -webkit-border-radius: 0;
    -moz-border-radius: 0;
    border-radius: 0;
    width: 100%;
    height: 100%;
}

.slider_<?php echo $slider_id; ?> .huge-it-slider > li img {
    max-width: 100%;
    max-height: 100%;
    margin: 0;
    cursor: pointer;
}

.slider_<?php echo $slider_id; ?> .huge-it-slide-bg, .slider_<?php echo $slider_id; ?> .huge-it-slider > li, .slider_<?php echo $slider_id; ?> .huge-it-slider > li > a, .slider_<?php echo $slider_id; ?> .huge-it-slider > li img {
<?php if(Hugeit_Slider_Options::get_slideshow_border_size() !== '0'){
    if($slider->get_navigate_by() === 'thumbnail'){
        echo '-webkit-border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px 0 0;';
        echo '-moz-border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px 0 0;';
        echo 'border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px 0 0;';
    } else {
        echo '-webkit-border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px;';
        echo '-moz-border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px;';
        echo 'border-radius: '.(Hugeit_Slider_Options::get_slideshow_border_radius() - 5).'px;';
    }
} ?>;
}

.huge-it-dot-wrap {
    position: absolute;
<?php switch(Hugeit_Slider_Options::get_navigation_position()){
	case 'top':
		echo 'top: 5px;';
		echo 'height: 20px;';
		break;
	case 'bottom':
		echo 'bottom: 5px;';
		echo 'height: auto;';
		break;
}
?> left: 50%;
    transform: translateX(-50%);
    z-index: 999;
}

.huge-it-dot-wrap a {
    -webkit-border-radius: 8px;
    -moz-border-radius: 8px;
    border-radius: 8px;
    cursor: pointer;
    display: block;
    float: left;
    height: 11px;
    margin: 2px !important;
    position: relative;
    text-align: left;
    text-indent: 9999px;
    width: 11px !important;
    background: <?php echo '#' . Hugeit_Slider_Options::get_dots_color(); ?>;
    box-shadow: none;
}

.huge-it-dot-wrap a.active:focus, .huge-it-dot-wrap a:focus,
.huge-it-thumb-wrap > a:focus, .huge-it-thumb-wrap > a.active:focus {
    outline: none;
}

.huge-it-dot-wrap a:hover {
    background: <?php echo '#' . Hugeit_Slider_Options::get_dots_color(); ?>;
    box-shadow: none !important;
}

.huge-it-dot-wrap a.active {
    background: <?php echo '#' . Hugeit_Slider_Options::get_active_dot_color(); ?>;
    box-shadow: none;
}

.huge-it-thumb-wrap {
    background: <?php echo '#' . Hugeit_Slider_Options::get_thumb_background_color();?>;
    height: <?php echo (Hugeit_Slider_Options::get_thumb_height() + 5).'px'; ?>;
    margin-left: 0;
<?php if($slider->get_navigate_by() === 'thumbnail'){
        echo 'margin-top: -7px;';
    } ?>;
}

.huge-it-thumb-wrap a.active img {
    border-radius: 5px;
    opacity: 1;
}

.huge-it-thumb-wrap > a {
    height: <?php echo Hugeit_Slider_Options::get_thumb_height() . 'px'; ?>;
    display: block;
    float: left;
    position: relative;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    background: <?php echo '#' . Hugeit_Slider_Options::get_thumb_passive_color(); ?>;
}

.huge-it-thumb-wrap a img {
    opacity: <?php echo 1 - Hugeit_Slider_Options::get_thumb_passive_color_transparency();?>;
    height: <?php echo Hugeit_Slider_Options::get_thumb_height() . 'px'; ?>;
    width: 100%;
    display: block;
    -ms-interpolation-mode: bicubic;
    box-shadow: none !important;
}

a.thumb_arr {
    position: absolute;
    height: 20px;
    width: 15px;
    bottom: <?php echo (Hugeit_Slider_Options::get_thumb_height() / 2 - 10). 'px'; ?>;
    z-index: 100;
    box-shadow: none;
}

a.thumb_prev {
    left: 5px;
    width: 15px;
    height: 20px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows1.png' ?>) left top no-repeat;
    background-size: 200%;
}

a.thumb_next {
    right: 5px;
    width: 15px;
    height: 20px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows1.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-grid {
    position: absolute;
    overflow: hidden;
    width: 100%;
    height: 100%;
    display: none;
}

.huge-it-gridlet {
    position: absolute;
    opacity: 1;
}

.huge-it-arrows .huge-it-next,
.huge-it-arrows .huge-it-prev {
    z-index: 1;
}

.huge-it-arrows:hover .huge-it-next,
.huge-it-arrows:hover .huge-it-prev {
    z-index: 2;
}

.huge-it-arrows {
    cursor: pointer;
    height: 40px;
    margin-top: -20px;
    position: absolute;
    top: 50%;
    /*transform: translateY(-50%);*/
    width: 40px;
    z-index: 2;
    color: rgba(0, 0, 0, 0);
    outline: none;
    box-shadow: none !important;
}

.huge-it-arrows:hover, .huge-it-arrows:active, .huge-it-arrows:focus,
.huge-it-dot-wrap a:hover, .huge-it-dot-wrap a:active, .huge-it-dot-wrap a:focus {
    outline: none;
    box-shadow: none !important;
}

.ts-arrow:hover {
    opacity: .95;
    text-decoration: none;
}

<?php
switch (Hugeit_Slider_Options::get_navigation_type()) {
	case 1: ?>
.huge-it-prev {
    left: 0;
    margin-top: -21px;
    height: 43px;
    width: 29px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -21px;
    height: 43px;
    width: 29px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;

}

<?php
break;
case 2: ?>
.huge-it-prev {
    left: 0;
    margin-top: -25px;
    height: 50px;
    width: 50px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -25px;
    height: 50px;
    width: 50px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-prev:hover {
    background-position: left -50px;
}

.huge-it-next:hover {
    background-position: right -50px;
}

<?php
break;
case 3: ?>
.huge-it-prev {
    left: 0;
    margin-top: -22px;
    height: 44px;
    width: 44px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -22px;
    height: 44px;
    width: 44px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-prev:hover {
    background-position: left -44px;
}

.huge-it-next:hover {
    background-position: right -44px;
}

<?php
break;
case 4:	?>
.huge-it-prev {
    left: 0;
    margin-top: -33px;
    height: 65px;
    width: 59px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -33px;
    height: 65px;
    width: 59px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-prev:hover {
    background-position: left -66px;
}

.huge-it-next:hover {
    background-position: right -66px;
}

<?php
break;
case 5: ?>
.huge-it-prev {
    left: 0;
    margin-top: -18px;
    height: 37px;
    width: 40px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -18px;
    height: 37px;
    width: 40px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 6: ?>
.huge-it-prev {
    left: 0;
    margin-top: -25px;
    height: 50px;
    width: 50px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -25px;
    height: 50px;
    width: 50px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-prev:hover {
    background-position: left -50px;
}

.huge-it-next:hover {
    background-position: right -50px;
}

<?php
break;
case 7:	?>
.huge-it-prev {
    left: 0;
    right: 0;
    margin-top: -19px;
    height: 38px;
    width: 38px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -19px;
    height: 38px;
    width: 38px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 8: ?>
.huge-it-prev {
    left: 0;
    margin-top: -22px;
    height: 45px;
    width: 45px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -22px;
    height: 45px;
    width: 45px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 9: ?>
.huge-it-prev {
    left: 0;
    margin-top: -22px;
    height: 45px;
    width: 45px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -22px;
    height: 45px;
    width: 45px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 10: ?>
.huge-it-prev {
    left: 0;
    margin-top: -24px;
    height: 48px;
    width: 48px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -24px;
    height: 48px;
    width: 48px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.huge-it-prev:hover {
    background-position: left -48px;
}

.huge-it-next:hover {
    background-position: right -48px;
}

<?php
break;
case 11: ?>
.huge-it-prev {
    left: 0;
    margin-top: -29px;
    height: 58px;
    width: 55px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -29px;
    height: 58px;
    width: 55px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 12: ?>
.huge-it-prev {
    left: 0;
    margin-top: -37px;
    height: 74px;
    width: 74px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -37px;
    height: 74px;
    width: 74px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 13: ?>
.huge-it-prev {
    left: 0;
    margin-top: -16px;
    height: 33px;
    width: 33px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -16px;
    height: 33px;
    width: 33px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 14: ?>
.huge-it-prev {
    left: 0;
    margin-top: -51px;
    height: 102px;
    width: 52px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -51px;
    height: 102px;
    width: 52px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 15: ?>
.huge-it-prev {
    left: 0;
    margin-top: -19px;
    height: 39px;
    width: 70px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -19px;
    height: 39px;
    width: 70px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 16: ?>
.huge-it-prev {
    left: 0;
    margin-top: -20px;
    height: 40px;
    width: 37px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left top no-repeat;
    background-size: 200%;
}

.huge-it-next {
    right: 0;
    margin-top: -20px;
    height: 40px;
    width: 37px;
    background: url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

<?php
break;
case 17: ?>
.huge-it-prev, .huge-it-next {
    background-color: rgba(0, 0, 0, .9);
    border-radius: 2px;
    color: #999;
    cursor: pointer;
    display: block;
    font-size: 22px;
    margin-top: -10px;
    padding: 8px 8px 7px;
    position: absolute;
    z-index: 1080
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: calc(50% - 11px);
    position: absolute;
}

.next_bg, .prev_bg {
    top: calc(50% - 20px)
}

.huge-it-next:hover .next_bg, .huge-it-prev:hover .prev_bg {
    fill: #fff;
}

.huge-it-next, .huge-it-prev {
    height: 50%;
    transform: translateY(-50%);
    background: none;
}

.huge-it-prev {
    left: 0;
}

.huge-it-next {
    right: 0;
}

<?php
break;
case 18: ?>
.huge-it-prev, .huge-it-next {
    background-color: rgba(0, 0, 0, .9);
    border-radius: 2px;
    color: #999;
    cursor: pointer;
    display: block;
    font-size: 22px;
    margin-top: -10px;
    padding: 8px 8px 7px;
    position: absolute;
    z-index: 1080
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: 29px;
    left: 29px;
    position: relative;
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: 20px
}

.huge-it-next:hover .next_bg, .huge-it-prev:hover .prev_bg {
    fill: #fff;
}

.huge-it-next, .huge-it-prev {
    height: 100px;
    width: 100px;
    border-radius: 50%;
    background: none;
}

.huge-it-next, .huge-it-prev {
    top: calc(50% - 50px) !important;
}

.huge-it-prev {
    left: 0;
}

.huge-it-next {
    right: 0;
}

<?php
break;
case 19: ?>
.huge-it-prev, .huge-it-next {
    background-color: rgba(0, 0, 0, .9);
    border-radius: 2px;
    color: #999;
    cursor: pointer;
    display: block;
    font-size: 22px;
    margin-top: -10px;
    padding: 8px 8px 7px;
    position: absolute;
    z-index: 1080
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: 29px;
    left: 29px;
    position: relative;
}

.next_bg, .prev_bg {
    top: 20px
}

.huge-it-next:hover .next_bg, .huge-it-prev:hover .prev_bg {
    fill: #fff;
}

.huge-it-next, .huge-it-prev {
    width: 100px;
    height: 100px;
    transform: translateY(-50%);
    border-radius: 5px;
    background: none;
}

.huge-it-prev {
    left: 0;
}

.huge-it-next {
    right: 0;
}

<?php
break;
case 20: ?>
.huge-it-prev, .huge-it-next {
    background-color: rgba(0, 0, 0, .9);
    border-radius: 2px;
    color: #999;
    cursor: pointer;
    display: block;
    font-size: 22px;
    margin-top: -10px;
    padding: 8px 8px 7px;
    position: absolute;
    z-index: 1080
}

.huge-it-next, .huge-it-prev {
    width: 30px;
    height: 90px;
    top: calc(50% - 45px) !important;
    border-radius: 10px;
    transition: 1s
}

.huge-it-next:hover, .huge-it-prev:hover {
    width: 160px;
    transition: 1s
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: 32px;
    position: absolute
}

.huge-it-next .next_bg {
    right: 3px
}

.huge-it-prev .prev_bg {
    left: 3px
}

.huge-it-prev {
    left: 0;
}

.huge-it-next {
    right: 0;
}

<?php
break;
case 21: ?>
.huge-it-prev, .huge-it-next {
    background-color: rgba(0, 0, 0, .9);
    border-radius: 2px;
    color: #999;
    cursor: pointer;
    display: block;
    font-size: 22px;
    margin-top: -10px;
    padding: 8px 8px 7px;
    position: absolute;
    z-index: 1080
}

.huge-it-next, .huge-it-prev {
    width: 30px;
    height: 90px;
    top: calc(50% - 35px) !important;
    border-radius: 10px;
    transition: 1s
}

.huge-it-next:hover, .huge-it-prev:hover {
    width: 250px;
    transition: 1s
}

.huge-it-next .next_bg, .huge-it-prev .prev_bg {
    top: 32px;
    position: absolute
}

.huge-it-next .next_bg {
    right: 3px
}

.huge-it-prev .prev_bg {
    left: 3px
}

.huge-it-next .next_title, .huge-it-prev .prev_title {
    width: 120px;
    position: relative;
    font-size: 20px;
    top: 20px;
    opacity: 0
}

.huge-it-next .next_title {
    left: 100px
}

.rwd-arrows_hover_effect-5 .huge-it-prev .prev_title {
    left: 20px
}

.huge-it-next:hover .next_title, .huge-it-prev:hover .prev_title {
    color: white;
    opacity: 1;
    transition: 2s
}

.huge-it-prev {
    left: 0;
}

.huge-it-next {
    right: 0;
}

<?php
break;
}
}?>

<?php if($slider->get_view() == 'carousel1' || $slider->get_view() == 'thumb_view'){
  switch (Hugeit_Slider_Options::get_navigation_type()) {
case 1: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-21px;
    height:43px;
    width:29px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-21px;
    height:43px;
    width:29px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;

}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-21px;
    height:29px;
    width:43px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) 40% 10% no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-21px;
    height:29px;
    width:43px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) 40% 100% no-repeat;
    background-size: 100%;

}
<?php
  break;
case 2: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Prev:hover {
    background-position:left -50px;
}

.rwd-Action > .rwd-Next:hover {
    background-position:right -50px;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left bottom no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Prev:hover {
    background-position:left 0;
}

.vertical .rwd-Action > .rwd-Next:hover {
    background-position:left -52px;
}
<?php
  break;
case 3: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:44px;
    width:44px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:44px;
    width:44px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Prev:hover {
    background-position:left -44px;
}

.rwd-Action > .rwd-Next:hover {
    background-position:right -44px;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:44px;
    width:44px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:44px;
    width:44px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left bottom no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Prev:hover {
    background-position:left 0;
}

.vertical .rwd-Action > .rwd-Next:hover {
    background-position:right -44px;
}
<?php
  break;
case 4:	?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-33px;
    height:65px;
    width:59px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-33px;
    height:65px;
    width:59px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Prev:hover {
    background-position:left -66px;
}

.rwd-Action > .rwd-Next:hover {
    background-position:right -66px;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-33px;
    height:59px;
    width:65px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-33px;
    height:59px;
    width:65px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left bottom no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Prev:hover {
    background-position:right 0;
}

.vertical .rwd-Action > .rwd-Next:hover {
    background-position:right -57px;
}
<?php
  break;
case 5: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-18px;
    height:37px;
    width:40px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-18px;
    height:37px;
    width:40px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-18px;
    height:40px;
    width:37px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-18px;
    height:40px;
    width:37px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 6: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Prev:hover {
    background-position:left -50px;
}

.rwd-Action > .rwd-Next:hover {
    background-position:right -50px;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-25px;
    height:50px;
    width:50px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left bottom no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Prev:hover {
    background-position:right 0;
}

.vertical .rwd-Action > .rwd-Next:hover {
    background-position:right -48px;
}
<?php
  break;
case 7:	?>
.rwd-Action > .rwd-Prev {
    left:0;
    right:0;
    margin-top:-19px;
    height:38px;
    width:38px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-19px;
    height:38px;
    width:38px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    right:0;
    margin-top:-19px;
    height:38px;
    width:38px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-19px;
    height:38px;
    width:38px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 8: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 9: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-22px;
    height:45px;
    width:45px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 10: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-24px;
    height:48px;
    width:48px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-24px;
    height:48px;
    width:48px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Prev:hover {
    background-position:left -48px;
}

.rwd-Action > .rwd-Next:hover {
    background-position:right -48px;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-24px;
    height:48px;
    width:48px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-24px;
    height:48px;
    width:48px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left bottom no-repeat;
    background-size: 200%;
}

.vertical .rwd-Action > .rwd-Prev:hover {
    background-position:right 0;
}

.vertical .rwd-Action > .rwd-Next:hover {
    background-position:right -48px;
}
<?php
  break;
case 11: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-29px;
    height:58px;
    width:55px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-29px;
    height:58px;
    width:55px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-29px;
    height:55px;
    width:58px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-29px;
    height:55px;
    width:58px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 12: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-37px;
    height:74px;
    width:74px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-37px;
    height:74px;
    width:74px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-37px;
    height:74px;
    width:74px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-37px;
    height:74px;
    width:74px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 13: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-16px;
    height:33px;
    width:33px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-16px;
    height:33px;
    width:33px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-16px;
    height:33px;
    width:33px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-16px;
    height:33px;
    width:33px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 14: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-51px;
    height:102px;
    width:52px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-51px;
    height:102px;
    width:52px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-51px;
    height:52px;
    width:102px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-51px;
    height:52px;
    width:102px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 15: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-19px;
    height:39px;
    width:70px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-19px;
    height:39px;
    width:70px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-19px;
    height:70px;
    width:39px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-19px;
    height:70px;
    width:39px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 16: ?>
.rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-20px;
    height:40px;
    width:37px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 200%;
}

.rwd-Action > .rwd-Next {
    right:0;
    margin-top:-20px;
    height:40px;
    width:37px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .rwd-Action > .rwd-Prev {
    left:0;
    margin-top:-20px;
    height:37px;
    width:40px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) left  top no-repeat;
    background-size: 100%;
}

.vertical .rwd-Action > .rwd-Next {
    right:0;
    margin-top:-20px;
    height:37px;
    width:40px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows' . Hugeit_Slider_Options::get_navigation_type() . '.png' ?>) right bottom no-repeat;
    background-size: 100%;
}
<?php
  break;
case 17: ?>
.rwd-Prev, .rwd-Next {top: calc(50% + 11px) !important;background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:calc(50% - 11px);position:absolute;}
.next_bg,.prev_bg{top:calc(50% - 20px)}
.rwd-Next:hover .next_bg, .rwd-Prev:hover .prev_bg{fill:#fff;}
.rwd-Next,.rwd-Prev {height:50%;transform:translateY(-50%);background:none;}
.rwd-Prev {left:0;}
.rwd-Next {right:0;}
<?php
  break;
case 18: ?>
.rwd-Prev, .rwd-Next {background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:-5px !important;position:relative;}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:20px}
.rwd-Next:hover .next_bg, .rwd-Prev:hover .prev_bg{fill:#fff;}
.rwd-Next, .rwd-Prev {height:100px;width:100px;border-radius:50%;background:none;}
.rwd-Next, .rwd-Prev{top: 50% !important;}
.rwd-Prev {left:0;}
.rwd-Next {right:0;}
<?php
  break;
case 19: ?>
.rwd-Prev, .rwd-Next {top: calc(50% + 11px) !important;background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:-5px;position:relative;}
.next_bg, .prev_bg{top:20px}
.rwd-Next:hover .next_bg, .rwd-Prev:hover .prev_bg{fill:#fff;}
.rwd-Next, .rwd-Prev {width:100px;height:100px;transform:translateY(-50%);border-radius:5px;background:none;}
.rwd-Prev {left:0;}
.rwd-Next {right:0;}
<?php
  break;
case 20: ?>
.rwd-Prev, .rwd-Next {background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-Next, .rwd-Prev{width:30px;height:90px;top:50% !important;border-radius:10px;transition: 1s}
.rwd-Next:hover, .rwd-Prev:hover{width:160px;transition: 1s}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:5px;position:absolute}
.rwd-Next .next_bg{right:3px}
.rwd-Prev .prev_bg{left:3px}
.rwd-Prev {left:0;}
.rwd-Next {right:0;}
<?php
  break;
case 21: ?>
.rwd-Prev, .rwd-Next {background-color:rgba(0,0,0,.9);border-radius:2px;color:#999;cursor:pointer;display:block;font-size:22px;margin-top:-10px;padding:8px 8px 7px;position:absolute;z-index:1080}
.rwd-Next, .rwd-Prev{width:30px;height:90px;top:50%;border-radius:10px;transition: 1s}
.rwd-Next:hover, .rwd-Prev:hover{width:250px;transition: 1s}
.rwd-Next .next_bg, .rwd-Prev .prev_bg{top:5px;position:absolute}
.rwd-Next .next_bg{right:3px}
.rwd-Prev .prev_bg{left:3px}
.rwd-Next .next_title, .rwd-Prev .prev_title{width:120px;position:relative;font-size:16px;top:-5px;opacity:0}
.rwd-Next .next_title{left:100px}
.rwd-arrows_hover_effect-5 .rwd-Prev .prev_title{left:20px}
.rwd-Next:hover .next_title, .rwd-Prev:hover .prev_title{color:white;opacity:1;transition:2s}
.rwd-Prev {left:0;}
.rwd-Next {right:0;}
<?php
  break;
}
?>
.rwd-SlideWrapper ul li {
    height: 100%;
}
.lightbox_on img {
    position: relative;
}
.rwd-SlideWrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.entry-content a, .entry-content a:hover, .entry-content a:active {
    box-shadow: unset;
}
.huge-it-slider, .rwd-SlideOuter {
    opacity: 0;
}
div[class*=slider-loader-] {
    background: rgba(0, 0, 0, 0) url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL . '/loading/loading' . Hugeit_Slider_Options::get_loading_icon_type() . '.gif'; ?>) no-repeat center;
    height: 90px;
    overflow: hidden;
    position: relative;
    z-index: 3;
}
.rwd-SlideOuter {
    overflow: hidden;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    max-width: 100%;
    max-height: 100%;
}
.huge_it_slider:before, .huge_it_slider:after {
    content: " ";
    display: table;
}
.huge_it_slider {
    overflow: hidden;
    margin: 0;
}
.rwd-SlideWrapper {
    max-width: 100%;
    overflow: hidden;
    position: relative;
}
.rwd-SlideWrapper > .huge_it_slider:after {
    clear: both;
}
.rwd-SlideWrapper .rwd-Slide {
    -webkit-transform: translate(0px, 0px);
    -ms-transform: translate(0px, 0px);
    transform: translate(0px, 0px);
    -webkit-transition: all 1s;
    -webkit-transition-property: -webkit-transform,height;
    -moz-transition-property: -moz-transform,height;
    transition-property: transform,height;
    -webkit-transition-duration: inherit !important;
    transition-duration: inherit !important;
    -webkit-transition-timing-function: inherit !important;
    transition-timing-function: inherit !important;
}
.rwd-SlideWrapper .rwd-Fade {
    position: relative;
}
.rwd-SlideWrapper .rwd-Fade > * {
    position: absolute !important;
    top: 0;
    left: 0;
    z-index: 9;
    margin-right: 0;
    width: 100%;
}
.rwd-SlideWrapper.usingCss .rwd-Fade > * {
    opacity: 0;
    -webkit-transition-delay: 0s;
    transition-delay: 0s;
    -webkit-transition-duration: inherit !important;
    transition-duration: inherit !important;
    -webkit-transition-property: opacity;
    transition-property: opacity;
    -webkit-transition-timing-function: inherit !important;
    transition-timing-function: inherit !important;
}
.rwd-SlideWrapper .rwd-Fade > *.active {
    z-index: 10;
}
.rwd-SlideWrapper.usingCss .rwd-Fade > *.active {
    opacity: 1;
}
.rwd-SlideOuter .rwd-Pager.rwd-pg {
    margin: 10px 0 0;
    padding: 0;
    text-align: center;
}
.rwd-SlideOuter .rwd-Pager.rwd-pg > li {
    cursor: pointer;
    display: inline-block;
    padding: 0 5px;
}
.rwd-SlideOuter .rwd-Pager.rwd-pg > li a {
    background-color: #222222;
    border-radius: 30px;
    display: inline-block;
    height: 8px;
    overflow: hidden;
    text-indent: -999em;
    width: 8px;
    position: relative;
    z-index: 99;
    -webkit-transition: all 0.5s linear 0s;
    transition: all 0.5s linear 0s;
}
.rwd-SlideOuter .rwd-Pager.rwd-pg > li:hover a, .rwd-SlideOuter .rwd-Pager.rwd-pg > li.active a {
    background-color: #428bca;
}
.rwd-SlideOuter .media {
    opacity: 0.8;
}
.rwd-SlideOuter .media.active {
    opacity: 1;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery {
    list-style: none outside none;
    padding-left: 0;
    margin: 0;
    overflow: hidden;
    transform: translate3d(0px, 0px, 0px);
    -moz-transform: translate3d(0px, 0px, 0px);
    -ms-transform: translate3d(0px, 0px, 0px);
    -webkit-transform: translate3d(0px, 0px, 0px);
    -o-transform: translate3d(0px, 0px, 0px);
    -webkit-transition-property: -webkit-transform;
    -moz-transition-property: -moz-transform;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery li {
    overflow: hidden;
    -webkit-transition: border-radius 0.12s linear 0s 0.35s linear 0s;
    transition: border-radius 0.12s linear 0s 0.35s linear 0s;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery:before, .rwd-SlideOuter .rwd-Pager.rwd-Gallery:after {
    content: " ";
    display: table;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery:after {
    clear: both;
}
.rwd-Action > a {
    width: 32px;
    display: block;
    top: 50%;
    height: 32px;
    cursor: pointer;
    position: absolute;
    z-index: 99;
    margin-top: -16px;
    opacity: 0.5;
    -webkit-transition: opacity 0.35s linear 0s;
    transition: opacity 0.35s linear 0s;
}
.thumbAction {
    position: relative;
}
.thumbAction > a {
    display: block;
    cursor: pointer;
    position: absolute;
    z-index: 99;
    opacity: 0.5;
    -webkit-transition: opacity 0.35s linear 0s;
    transition: opacity 0.35s linear 0s;
    width: 12px;
    height: 12px;
    background: red;
}
.thumbAction > .thumbPrev {
    margin-top: -5px;
    height: 23px;
    width: 14px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows1.png' ?>) left  top no-repeat;
    background-size: 200%;
}
.thumbAction > .thumbNext {
    margin-top: -5px;
    height: 23px;
    width: 14px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/arrows1.png' ?>) right top no-repeat;
    background-size: 200%;
}
.vertical .thumbAction > .thumbPrev {
    margin-top: -5px;
    height: 14px;
    width: 23px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows1.png' ?>) 40% 10% no-repeat;
    background-size: 100%;
}
.vertical .thumbAction > .thumbNext {
    margin-top: -5px;
    height: 14px;
    width: 23px;
    background:url(<?php echo HUGEIT_SLIDER_FRONT_IMAGES_URL .  '/arrows/varrows1.png' ?>) 40% 100% no-repeat;
    background-size: 100%;
}
.rwd-Action > a:hover, .thumbAction > a:hover {
    opacity: 1;
}
.rwd-Action > .rwd-Prev {
    background-position: 0 0;
    left: 10px;
}
.rwd-Action > .rwd-Next {
    background-position: -32px 0;
    right: 10px;
}
.rwd-Action > a.disabled, .thumbAction > a.disabled {
    pointer-events: none;
}
.cS-hidden {
    height: 1px;
    opacity: 0;
    filter: alpha(opacity=0);
    overflow: hidden;
}
.rwd-SlideOuter.vertical {
    position: relative;
}
.rwd-SlideOuter.vertical.noPager {
    padding-right: 0 !important;
}
.rwd-SlideOuter.vertical .rwd-Gallery {
    position: relative;
}
.rwd-SlideOuter.vertical .huge_it_slider > * {
    width: 100% !important;
    max-width: none !important;
}
.rwd-SlideOuter.vertical .rwd-Action > a {
    left: 50%;
    margin-left: -14px;
    margin-top: 0;
}
.rwd-SlideOuter.vertical .rwd-Action > .rwd-Next, .rwd-SlideOuter.vertical .thumbAction > .thumbNext {
    bottom: 10px;
    top: auto;
}
.rwd-SlideOuter.vertical .rwd-Action > .rwd-Prev, .rwd-SlideOuter.vertical .thumbAction > .thumbPrev {
    bottom: auto;
    top: 10px;
}
.rwd-SlideOuter.vertical .thumbAction > .thumbNext {
    bottom: 10px;
    top: auto;
}
.rwd-SlideOuter.vertical .thumbAction > .thumbPrev {
    bottom: auto;
    top: -490px;
}
.rwd-SlideOuter .huge_it_slider, .rwd-SlideOuter .rwd-Pager {
    padding-left: 0;
    list-style: none outside none;
}
.rwd-SlideOuter .rwd-fullscreen-on .huge_it_slider, .rwd-SlideOuter .rwd-fullscreen-on .rwd-Pager {
    height: 100%;
}

.rwd-SlideOuter .huge_it_slider > *,  .rwd-SlideOuter .rwd-Gallery li {
    float: left;
}
@-webkit-keyframes rightEnd {
    0% {
        left: 0;
    }

    50% {
        left: -15px;
    }

    100% {
        left: 0;
    }
}
@keyframes rightEnd {
    0% {
        left: 0;
    }

    50% {
        left: -15px;
    }

    100% {
        left: 0;
    }
}
@-webkit-keyframes topEnd {
    0% {
        top: 0;
    }

    50% {
        top: -15px;
    }

    100% {
        top: 0;
    }
}
@keyframes topEnd {
    0% {
        top: 0;
    }

    50% {
        top: -15px;
    }

    100% {
        top: 0;
    }
}
@-webkit-keyframes leftEnd {
    0% {
        left: 0;
    }

    50% {
        left: 15px;
    }

    100% {
        left: 0;
    }
}
@keyframes leftEnd {
    0% {
        left: 0;
    }

    50% {
        left: 15px;
    }

    100% {
        left: 0;
    }
}
@-webkit-keyframes bottomEnd {
    0% {
        bottom: 0;
    }

    50% {
        bottom: -15px;
    }

    100% {
        bottom: 0;
    }
}
@keyframes bottomEnd {
    0% {
        bottom: 0;
    }

    50% {
        bottom: -15px;
    }

    100% {
        bottom: 0;
    }
}
.rwd-SlideOuter .rightEnd {
    -webkit-animation: rightEnd 0.3s;
    animation: rightEnd 0.3s;
    position: relative;
}
.rwd-SlideOuter .leftEnd {
    -webkit-animation: leftEnd 0.3s;
    animation: leftEnd 0.3s;
    position: relative;
}
.rwd-SlideOuter.vertical .rightEnd {
    -webkit-animation: topEnd 0.3s;
    animation: topEnd 0.3s;
    position: relative;
}
.rwd-SlideOuter.vertical .leftEnd {
    -webkit-animation: bottomEnd 0.3s;
    animation: bottomEnd 0.3s;
    position: relative;
}
.huge_it_slider.isGrab > * {
    cursor: -webkit-grab;
    cursor: -moz-grab;
    cursor: -o-grab;
    cursor: -ms-grab;
    cursor: grab;
}
.huge_it_slider.isGrabbing > * {
    cursor: move;
    cursor: -webkit-grabbing;
    cursor: -moz-grabbing;
    cursor: -o-grabbing;
    cursor: -ms-grabbing;
    cursor: grabbing;
}
<?php } ?>

<?php if($slider->get_view() == 'carousel1'){ ?>
.rwd-SlideOuter .huge_it_slider {
    margin-top: 10px;
    overflow: initial;
}
.huge-it-slider.huge_it_slider li.active img {
    z-index: 999999999;
}
.rwd-SlideOuter, #slider_<?php echo $slider_id; ?>.huge-it-slider {
    opacity: 0;
}
.huge_it_youtube_iframe, .huge_it_vimeo_iframe {
    display: none;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery {

}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery li {
    opacity: <?php echo Hugeit_Slider_Options::get_thumb_passive_color_transparency();?>;
}
.rwd-SlideOuter .rwd-Pager.rwd-Gallery li.active, .rwd-SlideOuter .rwd-Pager.rwd-Gallery li:hover {
    opacity: 1;
}
.rwd-SlideOuter {
    background: <?php echo '#' . Hugeit_Slider_Options::get_thumb_passive_color();?>;
}
.rwd-SlideWrapper {
    background: <?php list($r,$g,$b) = array_map('hexdec',str_split(Hugeit_Slider_Options::get_slider_background_color(),2));
    $titleopacity = Hugeit_Slider_Options::get_slider_background_color_transparency();
    echo 'rgba('.$r.','.$g.','.$b.','.$titleopacity.')'; ?>;
}
.slider_<?php echo $slider_id; ?> {
    width: 100%;
    height: 100%;
    border: <?php echo Hugeit_Slider_Options::get_slideshow_border_size().'px'; ?> solid <?php echo '#'.Hugeit_Slider_Options::get_slideshow_border_color(); ?>;
    -webkit-border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
    -moz-border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
    border-radius: <?php echo Hugeit_Slider_Options::get_slideshow_border_radius().'px'; ?>;
<?php
switch($slider->get_position()){
    case 'center':
    echo 'margin: 0 auto;';
    break;
    case 'left':
    echo 'left: 0;';
    break;
    case 'right':
    echo 'margin-left: calc(100% - ' . $slider->get_width() . 'px);';
    break;
}
?>
}
.rwd-Action > .rwd-Next, .rwd-Action > .rwd-Prev,
.rwd-Action > .rwd-Next:hover, .rwd-Action > .rwd-Prev:hover {
    box-shadow: none;
}
.huge-it-slider.huge_it_slider li img {
    position: relative;
    opacity: 0.8;
    transition: all 300ms ease;
    vertical-align: super;
}
.huge-it-slider.huge_it_slider li.active img {
    opacity: 1;
    transform: scale(1.2);
}
.huge-it-slider.huge_it_slider li {
    list-style-type: none !important;
}
.rwd-SlideOuter .rwd-Pager.rwd-pg > li a {
    background: <?php echo '#' . Hugeit_Slider_Options::get_dots_color(); ?>;
    box-shadow: none;
}

.rwd-SlideOuter .rwd-Pager.rwd-pg > li.active a,
.rwd-SlideOuter .rwd-Pager.rwd-pg > li:hover a {
    background: <?php echo '#' . Hugeit_Slider_Options::get_active_dot_color(); ?>;
    box-shadow: none;
}
<?php } ?>

<?php if($slider->get_view() == 'thumb_view'){ ?>
.slider_<?php echo $slider_id; ?> {
    max-width: <?php echo $slider->get_width() . 'px'; ?>;
}

.huge-it-caption {
    position: absolute;
}
.rwd-fullscreen > svg {
    width: 25px;
    top: 20px;
    right: 10px;
    height: 32px;
    cursor: pointer;
    position: absolute;
    z-index: 99;
    margin-top: -16px;
    opacity: 0.5;
    -webkit-transition: opacity 0.35s linear 0s;
    transition: opacity 0.35s linear 0s;

}

svg#rwd-fullscreen-on {
    display: block;
}

svg#rwd-fullscreen-off {
    display: none;
}

.rwd-fullscreen-on #rwd-fullscreen-on {
    display: none;
}

.rwd-fullscreen-on #rwd-fullscreen-off {
    display: block;
}

.thumb_title, .thumb_description {
    width: calc(100% - 10px);
    text-align: left;
    margin: 0 5px 5px;
    color: black;
    line-height: 16.5px;
}
.vertical .thumb_title, .vertical .thumb_description {
    width: 145px;
    margin: 0 5px 5px 0;
}
.thumb_title {
    font-weight: bold;
    font-size:13px;
    font-family: 'Roboto', sans-serif;
}
.thumb_description {
    font-size:11px;
    font-family: 'Roboto', sans-serif;
}
.vertical .thumb_description {
    overflow: hidden;
}
.rwd-fullscreen-on .thumb_description {
    display: none;
}
.vertical .rwd-fullscreen-on .thumb_description {
    display: block;
}
.rwd-SlideWrapper #rwd-fullscreen-on:hover, .rwd-SlideWrapper #rwd-fullscreen-on:active,
.rwd-SlideWrapper #rwd-fullscreen-off:hover, .rwd-SlideWrapper #rwd-fullscreen-off:active,
.rwd-SlideWrapper .rwd-fullscreen.active svg {
    fill: black;
}
.rslider_iframe_cover {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 999;
}

.rwd-SlideWrapper iframe {
    height: 100%;
}

.rwd-fullscreen-on iframe {
    width: 100%;
}
.rwd-SlideOuter, .rwd-SlideOuter .rwd-Gallery {
    background: rgba(255, 255, 255, 1);
}
.rwd-SlideOuter .rwd-Gallery li.active, .rwd-SlideOuter .rwd-Gallery li:hover, .rwd-SlideOuter .rwd-Gallery li:active {
    transition: 500ms;
    background: rgba(182, 182, 182, 1);
}
.rwd-SlideOuter a img {
    box-shadow: none;
}
<?php } ?>
</style>