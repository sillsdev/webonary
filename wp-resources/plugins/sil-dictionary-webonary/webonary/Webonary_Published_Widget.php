<?php

class Webonary_Published_Widget extends WP_Widget
{
	/**
	 * Register widget with WordPress.
	 */
	public function __construct()
	{
		parent::__construct(
			'webonary_published_status',
			'Webonary Published Status',
			['description' => __('Webonary Published Status Widget', 'sil_dictionary')]
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget($args, $instance): void
	{
		$publication_status = (int)(get_option('publicationStatus') ?? 0);

		echo $args['before_widget'] ?? '';

		if ($publication_status > 0)
			echo self::getDictStageFlex($publication_status);

		echo $args['after_widget'] ?? '';
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ): void
	{
		echo '<p>There are no settings for this widget</p>';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ): array
	{
		return $new_instance;
	}

	public static function getDictStageFlex($status): string
	{
		$header = __('Publication Status', 'sil_dictionary');
		$rough = __('Rough draft', 'sil_dictionary');
		$self = __('Self-reviewed draft', 'sil_dictionary');
		$community = __('Community-reviewed draft', 'sil_dictionary');
		$consultant = __('Consultant approved', 'sil_dictionary');
		$no_formal = __('Finished (no formal publication)', 'sil_dictionary');
		$formal = __('Formally published', 'sil_dictionary');

		$status = (int)$status - 1;

		if ($status < 0 || $status > 5)
			$status = 0;

		$active = ['', '', '', '', '', ''];
		$active[$status] = 'active';


		return <<<HTML
<div class="publication-status">
    <h4 class="center">$header</h4>

    <div class="status-flex">
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[0]"></div>
                <div class="right-line purple-line"><span class="dot"></span></div>
                <p class="stage-text">$rough</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[1]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$self</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[2]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$community</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[3]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$consultant</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[4]"></div>
                <div class="purple-line"><span class="dot"></span></div>
                <p class="stage-text">$no_formal</p>
            </div>
        </div>
        <div class="stage">
            <div class="stage-inner">
                <div class="arrow $active[5]"></div>
                <div class="left-line purple-line"><span class="dot"></span></div>
                <p class="stage-text">$formal</p>
            </div>
        </div>
    </div>
</div>
HTML;

	}
}
