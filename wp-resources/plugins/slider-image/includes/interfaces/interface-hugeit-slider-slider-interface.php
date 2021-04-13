<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface Hugeit_Slider_Slider_Interface {

	/**
	 * Hugeit_Slider_Slider constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id = NULL );

	/**
	 * @return int
	 */
	public function get_id();

	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @param string $name
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_name( $name );

	/**
	 * @return int
	 */
	public function get_width();

	/**
	 * @param int $width
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_width( $width );

	/**
	 * @return int
	 */
	public function get_height();

	/**
	 * @param int $height
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_height( $height );

	/**
	 * @return int
	 */
	public function get_itemscount();

	/**
	 * @param int $itemscount
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_itemscount( $itemscount );
	
	/**
	 * @return string
	 */
	public function get_effect();

	/**
	 * @param string $effect
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_effect( $effect );

	/**
	 * @return int
	 */
	public function get_pause_time();

	/**
	 * @param int $pause_time
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_pause_time( $pause_time );

	/**
	 * @return int
	 */
	public function get_change_speed();

	/**
	 * @param int $change_speed
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_change_speed( $change_speed );

	/**
	 * @return string
	 */
	public function get_position();

	/**
	 * @param string $position
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_position( $position );

	/**
	 * @return int
	 */
	public function get_show_loading_icon();

	/**
	 * @param int $show_loading_icon
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_show_loading_icon( $show_loading_icon );

	/**
	 * @return string
	 */
	public function get_navigate_by();

	/**
	 * @param string $navigate_by
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_navigate_by( $navigate_by );

	/**
	 * @return int
	 */
	public function get_pause_on_hover();

	/**
	 * @param int $pause_on_hover
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_pause_on_hover( $pause_on_hover );

	/**
	 * @return int
	 */
	public function get_video_autoplay();

	/**
	 * @return int
	 */
	public function get_random();

	/**
	 * @param int $random
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_random( $random );

	/**
	 * @return int
	 */
	public function get_lightbox();

	/**
	 * @param int $lightbox
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_lightbox( $lightbox );

	/**
	 * @return string
	 */
	public function get_slide_effect();

	/**
	 * @param string $slide_effect
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_slide_effect( $slide_effect );

	/**
	 * @return string
	 */
	public function get_open_close_effect();

	/**
	 * @param string $open_close_effect
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_open_close_effect( $open_close_effect );

    /**
     * @return string
     */
    public function get_controls();

    /**
     * @param string $controls
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_controls( $controls );

    /**
     * @return string
     */
    public function get_fullscreen();

    /**
     * @param string $fullscreen
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_fullscreen( $fullscreen );

    /**
     * @return string
     */
    public function get_vertical();

    /**
     * @param string $vertical
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_vertical( $vertical );

    /**
     * @return string
     */
    public function get_thumbposition();

    /**
     * @param string $thumbposition
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbposition( $thumbposition );

    /**
     * @return string
     */
    public function get_thumbcontrols();

    /**
     * @param string $thumbcontrols
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbcontrols( $thumbcontrols );

    /**
     * @return string
     */
    public function get_dragdrop();

    /**
     * @param string $dragdrop
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_dragdrop( $dragdrop );

    /**
     * @return string
     */
    public function get_swipe();

    /**
     * @param string $swipe
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_swipe( $swipe );

    /**
     * @return string
     */
    public function get_thumbdragdrop();

    /**
     * @param string $thumbdragdrop
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbdragdrop( $thumbdragdrop );

    /**
     * @return string
     */
    public function get_thumbswipe();

    /**
     * @param string $thumbswipe
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbswipe( $thumbswipe );

    /**
     * @return string
     */
    public function get_titleonoff();

    /**
     * @param string $titleonoff
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_titleonoff( $titleonoff );

    /**
     * @return string
     */
    public function get_desconoff();

    /**
     * @param string $desconoff
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_desconoff( $desconoff );
    /**
     * @return string
     */
    public function get_titlesymbollimit();

    /**
     * @param string $titlesymbollimit
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_titlesymbollimit( $titlesymbollimit );
    /**
     * @return string
     */
    public function get_descsymbollimit();

    /**
     * @param string $descsymbollimit
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_descsymbollimit( $descsymbollimit );

    /**
     * @return string
     */
    public function get_pager();

    /**
     * @param string $pager
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_pager( $pager );

    /**
     * @return string
     */
    public function get_mode();

    /**
     * @param string $mode
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_mode( $mode );

    /**
     * @return string
     */
    public function get_vthumbwidth();

    /**
     * @param string $vthumbwidth
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_vthumbwidth( $vthumbwidth );

    /**
     * @return string
     */
    public function get_hthumbheight();

    /**
     * @param string $hthumbheight
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_hthumbheight( $hthumbheight );

    /**
     * @return string
     */
    public function get_thumbitem();

    /**
     * @param string $thumbitem
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbitem( $thumbitem );

    /**
     * @return string
     */
    public function get_thumbmargin();

    /**
     * @param string $thumbmargin
     *
     * @return Hugeit_Slider_Slider
     * @throws Exception
     */
    public function set_thumbmargin( $thumbmargin );
	/**
	 * @return string
	 */
	public function get_arrows_style();

	/**
	 * @param string $arrows_style
	 *
	 * @return Hugeit_Slider_Slider
	 * @throws Exception
	 */
	public function set_arrows_style( $arrows_style );
	
	/**
	 * Saves slider and it's slides.
	 *
	 * @return bool|int Inserted row id on success, false on failure.
	 */
	public function save();

	/**
	 * Add slide to $this->slides.
	 *
	 * @param Hugeit_Slider_Slide $slide
	 */
	public function add_slide( Hugeit_Slider_Slide $slide );

	/**
	 * Get slide by slide ID.
	 *
	 * @param int $id
	 *
	 * @return bool|Hugeit_Slider_Slide_Image
	 */
	public function get_slide($id);

	/**
	 * Returns count of slides in this slider.
	 *
	 * @return int
	 */
	public function get_slides_count();

	/**
	 * Delete slider by id.
	 *
	 * @param int $id Slider id.
	 *
	 * @return false|int
	 */
	public static function delete($id);
}