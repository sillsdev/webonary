<?php
/**
 * @var Hugeit_Slider_Slide_Video $slide
 * @var string $thumbnail_url
 * @var int $volume
 * @var int $quality
 * @var int $show_info
 * @var int $show_controls
 * @var int $control_color Vimeo video control color
 * @var string $site 'youtube' or 'vimeo'
 * @var string $url
*/
?>

<div class="image-block">
	<img src="<?php echo $thumbnail_url; ?>" alt="">
    <div class="play-icon <?php echo $site; ?>"></div>
</div>
<div class="slider-option">
	<table>
		<?php switch ($site) :
		case 'youtube' :
		?>
		<tr data-site="youtube">
			<td>
				<label>
					<?php _e('Quality', 'hugeit-slider'); ?>:
				</label>
				<select class="hugeit-slider-video-slide-quality">
					<option <?php selected($quality, '0'); ?> value="0"><?php _e('Auto', 'hugeit-slider'); ?></option>
					<option <?php selected($quality, '144'); ?> value="144">144p</option>
					<option <?php selected($quality, '240'); ?> value="240">240p</option>
					<option <?php selected($quality, '360'); ?> value="360">360p</option>
					<option <?php selected($quality, '480'); ?> value="480">480p</option>
					<option <?php selected($quality, '720'); ?> value="720">720p</option>
					<option <?php selected($quality, '1080'); ?> value="1080">1080p</option>
					<option <?php selected($quality, '1440'); ?> value="1440">1440p</option>
					<option <?php selected($quality, '2160'); ?> value="2160">2160p</option>
					<option <?php selected($quality, '4320'); ?> value="4320">4320p</option>
				</select>
				<label>
					<?php _e('Volume', 'hugeit-slider'); ?>:
					<input type="text" value="<?php echo $volume; ?>" class="hugeit-slider-video-slide-volume" data-slider-range="1,100" data-slider="true"  data-slider-highlight="true" />
				</label>
				<ul>
					<li>
						<label>
							<?php _e('Show Controls', 'hugeit-slider'); ?>:
							<input type="checkbox" class="hugeit-slider-video-slide-show-controles" id="" name="" value="1" <?php checked($show_controls); ?> />
							<span></span>
						</label>
					</li>
					<li>
						<label>
							<?php _e('Show Info', 'hugeit-slider'); ?>:
							<input type="checkbox" class="hugeit-slider-video-slide-show-info" id="" name="" value="1" <?php checked($show_info); ?> />
							<span></span>
						</label>
					</li>
				</ul>
			</td>
		</tr>
		<?php
		    break;
		case 'vimeo' : ?>
		<tr data-site="vimeo">
			<td>
				<label><?php _e('Elements Color', 'hugeit-slider'); ?>:<input type="text" value="<?php echo $control_color; ?>" class="color hugeit-slider-video-element-color" /></label>
			</td>
			<td>
				<label><?php _e('Volume', 'hugeit-slider'); ?>:<input type="text" value="<?php echo $volume; ?>" class="hugeit-slider-video-slide-volume" data-slider-range="1,100" data-slider="true"  data-slider-highlight="true" /></label>
			</td>
		</tr>
		<?php
            break;
		endswitch;
		?>
		<tr>
            <td colspan="2">
	            <input value="<?php echo $url; ?>" class="url_input" disabled />
	            <a href="#" class="remove-image"><?php _e('Remove Image', 'hugeit-slider'); ?></a></td>
		</tr>
	</table>
</div>
