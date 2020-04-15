<div class="wrap">

    <?php if(empty($fields['map_id'])){?>
        <div class="icon32 icon32-posts-page" style="background: url(<?php echo MAPSVG_PLUGIN_URL.'css/pin_gray.png'?>) no-repeat;"><br /></div>
        <h2 style="margin-bottom: 20px;">MapSVG plugin <sup style="font-size:  11px;"><?php echo MAPSVG_VERSION;?></sup>
            <a href="<?php echo admin_url('plugins.php?page=mapsvg-config&map_id=new')?>" class="add-new-h2">Add new map</a>
        </h2>
    <?php }else{?>
        <div class="icon32 icon32-posts-page" style="background: url(<?php echo MAPSVG_PLUGIN_URL.'css/pin_gray.png'?>) no-repeat;"><br /></div>
        <h2 style="margin-bottom: 20px;">
            <a href="<?php echo admin_url('plugins.php?page=mapsvg-config')?>">MapSVG plugin</a> <sup  style="font-size:  11px;"><?php echo MAPSVG_VERSION;?></sup>  &rarr; <span id="map-page-title"><?php if(empty($fields['title'])) echo 'New map'; else echo $fields['title'];?></span>
            <?php if($fields['map_id']!='new'){?><a href="<?php echo admin_url('plugins.php?page=mapsvg-config&noheader=true&delete_map='.$map_id)?>" class="add-new-h2">Delete this map</a><?php }?>

        </h2>
    <?php }?>


    <?php if(empty($fields['map_id'])){?>

    <div id="mapsvg-new-version" class="alert" style="display: none;">
        New version of MapSVG WordPress plugin is available. You can download it from <a href="http://codecanyon.net/item/mapsvg-interactive-vector-maps/2547255" target="_blank">CodeCanyon</a>.
    </div>



    <?php if($generated_maps){?>
    <!-- MENU -->
    <table class="table table-striped" id="mapsvg-table-maps">
        <thead>
            <th style="width: 30%">Name</th>
            <th style="width: 20%">Shortcode</th>
            <th>Actions</th>
        </thead>
        <tbody id="mapsvg-table">
                <?php foreach($generated_maps as $m){?>
                <?php setup_postdata($m);?>
                <tr data-id="<?php echo $m->ID?>" data-title="<?php echo $m->post_title?>">
                    <td class="mapsvg-map-title"><a href="?page=mapsvg-config&map_id=<?php echo $m->ID?>"><?php echo $m->post_title?></a></td>
                    <td class="mapsvg-shortcode">[mapsvg id=<?php echo $m->ID?>]</td>
                    <td class="mapsvg-action-buttons">
                        <a href="?page=mapsvg-config&map_id=<?php echo $m->ID?>" class="btn btn-small btn-info mapsvg-button-edit">Edit</a>
                        <a href="#" class="btn btn-small btn-success mapsvg-copy">Copy</a>
                        <a href="#" class="btn btn-small btn-danger mapsvg-delete">Delete</a>
                    </td>
                </tr>
                <?php }?>
        </tbody>
    </table>
    <?php }else{?>
        <a class="btn btn-danger" href="?page=mapsvg-config&map_id=new">Add new map</a>
    <?php }?>


    <?php }else{?>

    <!-- FORM -->
    <form id="mapform" action="" method="post" class="form-horizontal">

        <input type="hidden" name="save_mapsvg" value="1"/>
        <input type="hidden" id="default_width" value="<?php echo $default['m']['width']?>"/>
        <input type="hidden" id="default_height" value="<?php echo $default['m']['height']?>"/>
        <input type="hidden" id="default_viewbox_width" value="<?php echo $default['m']['viewBox'][2]?>"/>
        <input type="hidden" id="default_viewbox_height" value="<?php echo $default['m']['viewBox'][3]?>"/>
        <input type="hidden" name="map_id" value="<?php echo $fields['map_id']?>"/>


        <?php if(!$map_chosen):?>
        <div class="control-group">


        <div class="alert alert-info">
            You can add your own SVG maps to "/maps" folder inside of MapSVG plugin's folder.
            If you have added your own .svg map and it doesn't work as expected, please read
            <a href="http://map.karaliki.ru/_faq.html" target="_blank"><ins>FAQ</ins></a>.
        </div>

        <h4>Choose a map file</h4>


        <ul class="nav nav-tabs nav-stacked span4">
        <?php if($maps) foreach($maps as $m){?>
            <li><a href="?page=mapsvg-config&map_id=new&map=<?php echo $m?>"><?php echo $m?></a></li>
        <?php }?>
        </ul>



        <?php else:?>

        <div id="mapsvg-alert-shortcode" class="alert alert-info" <?php if($map_id=='new'){?>style="display: none"<?php }?>>
            Add this shortcode to a page: [mapsvg id=<span id="mapsvg-shortcode"><?php echo $map_id?></span>]
        </div>

        <ul class="nav nav-tabs" id="myTab">
          <li class="active"><a href="#tab_settings">Main Settings</a></li>
          <li><a href="#tab_colors">Colors</a></li>
          <li><a href="#tab_regions">Regions</a></li>
          <li><a href="#tab_marks">Markers</a></li>
          <li><a href="#tab_events">JavaScript</a></li>
        </ul>

        <div class="tab-content">
         <!-- TAB 1 -->
          <div class="tab-pane active" id="tab_settings">
                <!-- MAIN SETTINGS -->
                <legend>Main settings</legend>
                <div class="control-group">
                <label>Map file</label>
                <div class="controls">
                    <input type="hidden" name="mapfile" value="<?php echo $fields['mapfile']?>"/>
                    <input type="text" disabled="true" value="<?php echo $fields['mapfile']?>"/>
                </div>
                </div>



                <div class="control-group">
                    <label class="control-label">Map Title</label>
                    <div class="controls">
                        <input type="text" name="title" value="<?php echo $fields['title']?>"/>
                        <p class="help-block">Title for menu in this Contol Panel</p>
                    </div>
                </div>

                <div class="control-group">
                    <div class="controls">

                    <label class="checkbox">
                      <input type="checkbox" name="ratio_def" id="keep_ratio" <?php if((bool)$fields['ratio_def']) echo 'checked="true"';?> /> Keep map's default width / height ratio
                    </label>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">Width</label>
                    <div class="controls">
                        <input id="map_width" type="text" name="m[width]" value="<?php echo $fields['m']['width']?>" autocomplete="off"/>
                    </div>
                </div>

                <div class="control-group">
                <label>Height</label>
                <div class="controls">
                    <input id="map_height" type="text" name="m[height]" value="<?php echo $fields['m']['height']?>"  autocomplete="off"/>
                </div>
                </div>

                <div class="control-group">
                <label>ViewBox</label>
                <div class="controls">
                    <input type="text" disabled="" id="viewbox_disabled" value="<?php echo $fields['viewBox_i']?>"/>
                    <input type="hidden" id="viewbox_x" name="m[viewBox][0]" value="<?php echo $fields['m']['viewBox'][0]?>"/>
                    <input type="hidden" id="viewbox_y" name="m[viewBox][1]" value="<?php echo $fields['m']['viewBox'][1]?>"/>
                    <input type="hidden" id="viewbox_w" name="m[viewBox][2]" value="<?php echo $fields['m']['viewBox'][2]?>"/>
                    <input type="hidden" id="viewbox_h" name="m[viewBox][3]" value="<?php echo $fields['m']['viewBox'][3]?>"/>

                    <a href="#" class="btn" id="setViewBox">Edit</a>

                    <p id="viewbox-help-block" class="help-block">
                        ViewBox defines map's initial position and zoom level.
                    </p>


                    <div id="mapsvg-viewbox-edit" class="well hidden" style="margin-top: 10px;">
                        <p id="viewbox-help-block" class="help-block" style="margin: 0 0 10px 0;">
                            Pan with mouse cursor, zoom with mousewheel. Click "Set viewBox" when done.
                        </p>
                        <div class="control-group">
                            <a href="#" class="btn btn-small btn-primary" id="saveViewBox">Set viewBox</a>
                            <!--<a href="#" class="btn btn-small" id="saveViewBox">Fit map to container</a>-->
                        </div>
                        <div id="mapsvg-viewbox"></div>
                    </div>
                </div>
                </div>


                <div class="control-group">
                <label>Responsive</label>
                <div class="controls">
                    <input type="hidden" name="m[responsive]" id="responsive-switch" value="<?php echo (int)$fields['m']['responsive']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?php if((int)$fields['m']['responsive']) echo 'active';?>" onclick="jQuery('#responsive-switch').val(1);">On</a>
                      <a class="btn btn-small <?php if(!(int)$fields['m']['responsive']) echo 'active';?>" onclick="jQuery('#responsive-switch').val(0);">Off</a>
                    </div>
                    <p class="help-block">
                    When enabled, size will automatically adjust for small screens.<br />
                    Width and Height parameters will be set as limiting "max-width" and "max-height".<br />

                    </p>
                </div>
                </div>


                <div class="control-group">
                <label>Pan</label>
                <div class="controls">
                    <input type="hidden" name="m[pan]" id="pan-switch" value="<?php echo (int)$fields['m']['pan']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?php if((int)$fields['m']['pan']) echo 'active';?>" onclick="jQuery('#pan-switch').val(1);">On</a>
                      <a class="btn btn-small <?php if(!(int)$fields['m']['pan']) echo 'active';?>" onclick="jQuery('#pan-switch').val(0);">Off</a>
                    </div>
                    <p class="help-block">Enable map scrolling by mouse pointer</p>
                </div>
                </div>

                <div class="control-group">
                <label>Zoom</label>
                <div class="controls">
                    <input type="hidden" name="m[zoom]" id="zoom-switch" value="<?php echo (int)$fields['m']['zoom']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?php if((int)$fields['m']['zoom']) echo 'active';?>" onclick="jQuery('#zoom-switch').val(1);">On</a>
                      <a class="btn btn-small <?php if(!(int)$fields['m']['zoom']) echo 'active';?>" onclick="jQuery('#zoom-switch').val(0);">Off</a>
                    </div>
                    <p class="help-block">Enable map zooming by mousewheel</p>
                </div>
                </div>


                <div class="control-group">
                <label>Zoom Buttons</label>
                <div class="controls">
                    <input type="hidden" name="m[zoomButtons][show]" id="zoombtn-switch" value="<?php echo (int)$fields['m']['zoomButtons']['show']?>"/>
                    <input type="hidden" name="m[zoomButtons][location]" id="zoomloc-switch" value="<?php echo $fields['m']['zoomButtons']['location']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?php if(!(int)$fields['m']['zoomButtons']['show']) echo 'active';?>" onclick="jQuery('#zoombtn-switch').val(0);">Hide</a>
                      <a class="btn btn-small <?php if((int)$fields['m']['zoomButtons']['show'] && $fields['m']['zoomButtons']['location'] == 'left') echo 'active';?>" onclick="jQuery('#zoombtn-switch').val(1);jQuery('#zoomloc-switch').val('left');">Left</a>
                      <a class="btn btn-small <?php if((int)$fields['m']['zoomButtons']['show'] && $fields['m']['zoomButtons']['location'] == 'right') echo 'active';?>" onclick="jQuery('#zoombtn-switch').val(1);jQuery('#zoomloc-switch').val('right');">Right</a>
                    </div>
                    <p class="help-block">Show buttons on left/right side of the map or hide them. Buttons are visible only if Zoom is "On".</p>
                </div>
                </div>

                <div class="control-group">
                <label>Zoom Delta</label>
                <div class="controls">
                    <input type="text" name="m[zoomDelta]" id="zoom-delta" value="<?php echo $fields['m']['zoomDelta']?>"/>
                    <p class="help-block">Amount of zoom increase for each level. <br />
                    Lower values (1.2, 1.3 etc..) will give more smooth zooming.<br />
                </div>
                </div>


                <div class="control-group">
                <label>Zoom Limits</label>
                <div class="controls">
                    <input type="number" class="input-mini" name="m[zoomLimit][0]" value="<?php echo $fields['m']['zoomLimit'][0];?>"/> -
                    <input type="number" class="input-mini" name="m[zoomLimit][1]" value="<?php echo $fields['m']['zoomLimit'][1];?>"/>

                    <p class="help-block">Lower and upper limit of zoom levels. <br />
                    Initial zoom = step "0", zoom out 1 level = "-1", zoom in one level = "1".

                </div>
                </div>


                <div class="control-group">
                <label>Popover Width</label>
                <div class="controls">
                    <input type="text" name="m[popover][width]" value="<?php echo $fields['m']['popover']['width']?>"/>
                    <p class="help-block">Width of information block which appears "on click". Default is "auto", but you can set actual width in pixels.</p>

                </div>
                </div>

                <div class="control-group">
                <label>Popover Height</label>
                <div class="controls">
                    <input type="text" name="m[popover][height]" value="<?php echo $fields['m']['popover']['height']?>"/>
                    <p class="help-block">Height of information block which appears "on click". Default is "auto", but you can set actual width in pixels.</p>
                </div>
                </div>


                <!--
                <div class="control-group">
                <label>ViewBox [x]</label>
                <div class="controls">
                    <input type="text" id="viewbox_x" name="m[viewBox][0]" value="<?php echo $fields['m']['viewBox'][0]?>"/>
                    <p class="help-block">Change Viewbox if you want to show only a portion of map. <br />Viewbox determines a rectangle window on the map, starting from [x] and [y], with [width] and [height].</p>
                </div>
                </div>
                <div class="control-group">
                <label>ViewBox [y]</label>
                <div class="controls">
                    <input type="text" id="viewbox_y" name="m[viewBox][1]" value="<?php echo $fields['m']['viewBox'][1]?>"/>
                </div>
                </div>
                <div class="control-group">
                <label>ViewBox [width]</label>
                <div class="controls">
                    <input type="text" id="viewbox_width" name="m[viewBox][2]" value="<?php echo $fields['m']['viewBox'][2]?>"/>
                </div>
                </div>
                <div class="control-group">
                <label>ViewBox [height]</label>
                <div class="controls">
                    <input type="text" id="viewbox_height" name="m[viewBox][3]" value="<?php echo $fields['m']['viewBox'][3]?>"/>
                </div>
                </div>
                -->

                <div class="control-group">
                <label>Tooltips mode</label>
                <div class="controls">
                    <select name="m[tooltipsMode]">
                            <option value="">Off</option>
                            <option value="names" <?php if($fields['m']['tooltipsMode']=='names') echo 'selected';?>>Show region names</option>
                            <option value="custom" <?php if($fields['m']['tooltipsMode']=='custom') echo 'selected';?>>Show custom tooltips</option>
                            <option value="combined" <?php if($fields['m']['tooltipsMode']=='combined') echo 'selected';?>>Combined (show custom or name if no custom)</option>
                    </select>
                </div>
                </div>

                <div class="control-group">
                <label>Cursor</label>
                <div class="controls">
                    <select name="m[cursor]">
                        <option value="default" <?php if($fields['m']['cursor']=='default') echo 'selected="selected"'?>>default</option>
                        <option value="pointer" <?php if($fields['m']['cursor']=='pointer') echo 'selected="selected"'?>>pointer</option>
                        <option value="help" <?php if($fields['m']['cursor']=='help') echo 'selected="selected"'?>>help</option>
                    </select>
                    <p class="help-block">Cursor style when hover on regions.</p>
                </div>
                </div>

                <div class="control-group">
                <label>Multi Select</label>
                <div class="controls">
                    <input type="hidden" name="m[multiSelect]" id="multiselect-switch" value="<?php echo (int)$fields['m']['multiSelect']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?php if((int)$fields['m']['multiSelect']) echo 'active';?>" onclick="jQuery('#multiselect-switch').val(1);">On</a>
                      <a class="btn btn-small <?php if(!(int)$fields['m']['multiSelect']) echo 'active';?>" onclick="jQuery('#multiselect-switch').val(0);">Off</a>
                    </div>
                    <p class="help-block">Enable multiple select of regions (click a region = select, click again the same region = deselect)</p>
                </div>
                </div>


                <div class="control-group">
                <label>Preloader text</label>
                <div class="controls">
                    <input type="text" name="m[loadingText]" value="<?php echo $fields['m']['loadingText']?>"/>
                </div>
                </div>


          </div>

          <!-- TAB 2 -->
          <div class="tab-pane" id="tab_colors">

                <!-- COLORS -->
                <legend>Colors</legend>

                <!--
                <div class="control-group">
                <label>Keep source styles</label>
                <div class="controls">
                    <input type="hidden" name="m[keepSourceStyles]" id="keepSourceStyles-switch" value="<?//php echo (int)$fields['m']['keepSourceStyles']?>"/>
                    <div class="btn-group" data-toggle="buttons-radio">
                      <a class="btn btn-small <?//php if((int)$fields['m']['keepSourceStyles']) echo 'active';?>" onclick="jQuery('#keepSourceStyles-switch').val(1);">On</a>
                      <a class="btn btn-small <?//php if(!(int)$fields['m']['keepSourceStyles']) echo 'active';?>" onclick="jQuery('#keepSourceStyles-switch').val(0);">Off</a>
                    </div>
                    <p class="help-block">
                        On: load colors and stroke styles from SVG file.<br />
                        Off: use plugin's default colors.
                    </p>
                </div>
                </div>
                -->

                <div class="control-group">
                    <label>Background</label>
                    <div class="controls">
                        <input class="cpicker" type="text" name="m[colors][background]" value="<?php echo $fields['m']['colors']['background']?>" placeholder="#E1F1F1"/>
                        <p class="help-block">Enter "transparent" for transparent background.</p>
                    </div>
                </div>

                <div class="control-group">
                <label>Base</label>
                <div class="controls">
                    <input class="cpicker" type="text" name="m[colors][base]" value="<?php echo $fields['m']['colors']['base']?>"  placeholder="#EEEEEE"/>
                </div>
                </div>

                <div class="control-group">
                <label>Disabled</label>
                <div class="controls">
                    <input class="cpicker" type="text" name="m[colors][disabled]" value="<?php echo $fields['m']['colors']['disabled']?>"  placeholder="#FFFFFF"/>
                </div>
                </div>


                <div class="control-group">
                <label>Strokes</label>
                <div class="controls">
                    <input class="cpicker" type="text" name="m[colors][stroke]" value="<?php echo $fields['m']['colors']['stroke']?>" placeholder="#7EADC0"/>
                </div>
                </div>


                <div class="control-group">
                <label>Hover</label>
                <div class="controls">
                    <input class="cpicker" type="text" name="m[colors][hover]" value="<?php echo $fields['m']['colors']['hover']?>" placeholder="#7EADC0"/>
                    <p class="help-block">
                        Change color or brightness on mouse hover. <br />
                        There are 2 modes: 1) change to other color; 2) increase / decrease brithness of base color.<br />
                        Enter any color in HEX format to change to other color: #FF2233<br />
                        Enter some positive number to increase brightness: 10<br />
                        Enter some negative number to decrease brightness: -20<br />
                        Bigger number = more brightness increase / decrease
                    </p>


                </div>
                </div>

                <div class="control-group">
                <label>Selected</label>
                <div class="controls">
                    <input class="cpicker" type="text" name="m[colors][selected]" value="<?php echo $fields['m']['colors']['selected']?>" placeholder="#7EADC0"/>
                    <p class="help-block">
                        Change color or brightness of selected region: 2 modes, same as for "Hover" field above.
                    </p>

                </div>
                </div>

          </div>

          <!-- TAB 3 -->
          <div class="tab-pane" id="tab_regions">
                <?php if(isset($fields['m']['regions'])){?>
                    <!-- REGIONS -->
                    <legend>Regions</legend>
                    
                    <!--
                    <label class="checkbox" style="margin-bottom: 15px;">
                        <input type="checkbox" name="m[disableAll]" value="true"/> Disable all regions
                    </label>
                    -->

                    <table class="table-bordered table-condensed table-striped">
                        <thead>
                            <th>Regions 
                            <label class="checkbox" style="font-weight: normal;">
                                <input type="checkbox" value="true" id="disable_all_regions"/> disable all
                            </label>
                            </th>
                            
                            
                            
                            
                            <th>Color</th>
                            <th>Tooltip</th>
                            <th>Popover</th>
                            <th>URL</th>
                            <th>Custom Data</th>

                        </thead>
                        <tbody>
                            <?php foreach($fields['m']['regions'] as $id=>$region){?>
                                <tr>
                                    <td>
                                        <div style="width:  150px; overflow: hidden;">
                                            <strong style="line-height: 30px;"> <?php echo $id?></strong><br />
                                            
                                            
                                            <!--
                                            <div class="btn-group" data-toggle="buttons-radio">                                                
                                              <a class="btn btn-mini">disabled</a>
                                                <input type="hidden" value="1" class="region_disable" name="m[regions][<?php echo $id?>][disabled]" <?php if($region['disabled']) echo 'checked'?>/>
                                                <input type="hidden" value="1" class="region_select" name="m[regions][<?php echo $id?>][selected]" <?php if($region['selected']) echo 'checked'?>/>
                                              <a class="btn btn-mini">selected</a>
                                            </div>
                                            -->
                                                                                        
                                            <div style="font-size:  10px !important;">
                                            <label class="checkbox pull-left">
                                                <input type="checkbox" class="region_disable" name="m[regions][<?php echo $id?>][disabled]" <?php if($region['disabled']) echo 'checked'?>/> disabled
                                            </label>
                                            <label class="checkbox pull-left" style="padding-left: 25px;">
                                                <input type="checkbox" class="region_select" name="m[regions][<?php echo $id?>][selected]" <?php if($region['selected']) echo 'checked'?> /> selected
                                            </label>
                                            </div>
                                                                                        
                                            
                                        </div>
                                    </td>
                                    <!--
                                    <td style="text-align: center;"><input type="checkbox" value="1" class="region_disable" name="m[regions][<?php echo $id?>][disabled]" <?php if($region['disabled']) echo 'checked'?>/></td>
                                    <td style="text-align: center;"><input type="checkbox" value="1" class="region_select" name="m[regions][<?php echo $id?>][selected]" <?php if($region['selected']) echo 'checked'?>/></td>
                                    -->
                                    <td><input class="cpicker input-small" type="text" placeholder="#FF2233" class="input-small" name="m[regions][<?php echo $id?>][attr][fill]" value="<?php echo $region['attr']['fill']?>"/></td>
                                    <td><textarea placeholder="Tooltip" name="m[regions][<?php echo $id?>][tooltip]"><?php echo $region['tooltip']?></textarea></td>
                                    <td><textarea placeholder="Popover" name="m[regions][<?php echo $id?>][popover]"><?php echo $region['popover']?></textarea></td>
                                    <td>
                                        <input type="text" placeholder="http://" name="m[regions][<?php echo $id?>][attr][href]" value="<?php echo $region['attr']['href']?>"/>
                                        <label class="checkbox"> 
                                            <input id="mapsvg-url-target" type="hidden" name="m[regions][<?php echo $id?>][attr][target]" value="<?php echo !empty($region['attr']['target']) && !empty($region['attr']['href']) ? $region['attr']['target'] : ''; ?>" />
                                            <input type="checkbox" onchange="jQuery(this).prev().val( jQuery(this).is(':checked') ? 'blank' : '' );" <?php echo !empty($region['attr']['target']) && !empty($region['attr']['href']) ? 'checked': ''; ?> /> In new tab
                                        </label>
                                    </td>
                                    <td>
                                        <textarea name="m[regions][<?php echo $id?>][data]"><?php echo !empty($region['data']) ? $region['data'] : '';?></textarea>
                                    </td>
                                </tr>
                            <?php }?>
                        </tbody>

                    </table>
                <?php }?>

          </div>

          <!-- TAB 4 -->
          <div class="tab-pane tab-marks" id="tab_marks">

            <!-- Marks -->
            <legend>Markers</legend>

            <?php if(!empty($mapsvg_error) && !empty($mapsvg_error['file'])){?>            
                <div class="alert alert-error">
                    <?php echo $mapsvg_error['file'];?>
                </div>
            <?php }?>
            
         
            
            <div class="row-fluid">
            
                <div class="span9">

                    <p>Click on the map to place a marker.</p>
                    <p class="help-block">Free marker icons provided by <a href="http://p.yusukekamiyamane.com">p.yusukekamiyamane.com</a> and <a href="http://www.tabsicons.com">tabsicons.com</a>.
                    You can use your own marker images (.png, .jpeg) - just put them to /markers folder inside of plugin's folder. There are no size requirements, use any which fits you.
                    Marker must point to its bottom side (like a pin) for exact marker position adjustment at different map sizes.<br /><br /></p>
                    
                </div>
                
                <?php if($fields['mapfile']=='world_high.svg' || $fields['mapfile']=='world_with_states.svg'){?>
                <div class="span3">                
                    <div class="well pull-right" style="margin-top:  0; text-align: center;">                
                        <p>
                            <a id="btn-mapsvg-import-csv" class="btn btn-danger btn-import" data-import-objects="marks">Import CSV file</a>
                        </p>
                        <a href="<?php echo MAPSVG_PLUGIN_URL . 'marks.xlsx';?>">Download Excel form</a><br />
                    </div>                
                </div>
                <?php }?>
                
            
            </div>


            <div id="mapsvg"></div>


            <?php if(false){//$fields['mapfile']=='world_high.svg'){?>

            <?php if(false){//$fields['mapfile']=='world_high.svg'){?>
            <p>
            You can put markers to World Map by latitude-longitude coordinates.<br />
            Example for London - Latitude: 51.50746, Longitude: -0.12085</p>
            <p>You can get coordinates of any location from <a href="https://maps.google.com/">Google Maps</a>. <br />
            Right-click at any point, choose "What's there?" from pop-up menu and copy coordinates from search field.
            </p><br />

            <div class="control-group">
            <label class="control-label">Coordinates of Markers</label>
            <div class="controls">
              <label class="radio">
                <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2" checked="" />
                x / y
              </label>
              <label class="radio">
                <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" />
                Latitude / Longitude (works only with World Map)
              </label>
            </div>
          </div>
          <?php }?>
            <table class="table-bordered table-condensed table-striped">
                <thead>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>X</th>
                    <th>Y</th>
                    <th>Color</th>
                    <th>Label</th>
                    <th>Tooltip</th>
                    <th>URL</th>
                    <th>URL Target</th>
                </thead>
                <tbody>

                    <?php if($fields['m']['marks']) foreach($fields['m']['marks'] as $i=>$m){?>
                        <tr>
                            <td><input type="text" class="input-small" name="m[marks][<?php echo $i?>][c][0]"/></td>
                            <td><input type="text" class="input-small" name="m[marks][<?php echo $i?>][c][1]"/></td>
                            <td><input type="text" class="input-small" name="m[marks][<?php echo $i?>][xy][0]"/></td>
                            <td><input type="text" class="input-small" name="m[marks][<?php echo $i?>][xy][1]"/></td>
                            <td><input class="cpicker input-small" type="text" placeholder="#FF2233" name="m[marks][<?php echo $m?>][attr][fill]" value="<?php echo $m['attr']['fill']?>"/></td>
                            <td><input type="text" name="m[marks][<?php echo $i?>][label]"/></td>
                            <td><input type="text" name="m[marks][<?php echo $i?>][tooltip]"/></td>
                            <td><input type="text" placeholder="http://" name="m[marks][<?php echo $i?>][attr][href]" value="<?php echo $r['attr']['href']?>"/></td>
                            <td><input type="text" placeholder="blank" class="input-small" name="m[regions][<?php echo $id?>][attr][target]" value="<?php echo $r['attr']['target']?>"/></td>
                        </tr>
                    <?php }else{
                          $i = 0;
                     }?>
                        <tr>
                            <td><input type="text" class="input-mini" name="m[marks][<?php echo $i+1?>][c][0]"/></td>
                            <td><input type="text" class="input-mini" name="m[marks][<?php echo $i+1?>][c][1]"/></td>
                            <td><input type="text" class="input-mini" name="m[marks][<?php echo $i?>][xy][0]"/></td>
                            <td><input type="text" class="input-mini" name="m[marks][<?php echo $i?>][xy][1]"/></td>
                            <td><input class="cpicker input-small" type="text" placeholder="#FF2233" name="m[marks][<?php echo $m?>][attr][fill]"/></td>
                            <td><input type="text" name="m[marks][<?php echo $i+1?>][label]"/></td>
                            <td><input type="text" name="m[marks][<?php echo $i+1?>][tooltip]"/></td>
                            <td><input type="text" placeholder="http://" name="m[marks][<?php echo $i+1?>][attr][href]"/></td>
                            <td><input type="text" placeholder="blank" class="input-small" name="m[marks][<?php echo $i+1?>][attr][target]" /></td>
                        </tr>
                </tbody>

            </table>

            <?php }?>

          </div>

          <div class="tab-pane" id="tab_events">
                <!-- EVENTS -->
                <legend>JavaScript event handlers</legend>

                <p>Attributes available for each event handler:</p>

                <div class="well" style="padding:  10px;">

                    <ul style="margin: 0;">
                        <li><span class="label label-info">event</span> - javascript event object</li>
                        <li><span class="label label-info">mapsvg</span> - object containing all methods of mapsvg plugin</li>
                        <li><span class="label">this</span> - current region (<a href="http://raphaeljs.com/reference.html">Raphael.js "Element" object</a>)</li>
                    </ul>
                </div>


                <p>Example for <strong>onClick</strong> event handler:</p>

<pre id="ehandler_example">
<strong>function</strong>(event, mapsvg){
    alert([event.offsetX, event.offsetY]) // Show coordinates of click
    alert(this.name); // Show country name
    <strong>this</strong>.attr({fill: "#ff0000"}); // Fill country with red color
    <strong>this</strong>.hide(); // Hide clicked country
    mapsvg.setZoom(true); // Enable zooming
}
</pre><br />

                <div class="control-group">
                <label>onClick</label>
                <div class="controls">
                <textarea class="input-xxlarge" rows="8" name="events[onClick]"><?php echo $fields['events']['onClick']?></textarea>
                </div>
                </div>

                <div class="control-group">
                    <label>mouseOver</label>
                    <div class="controls">
                    <textarea class="input-xxlarge" rows="8" name="events[mouseOver]"><?php echo $fields['events']['mouseOver']?></textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label>mouseOut</label>
                    <div class="controls">
                    <textarea class="input-xxlarge" rows="8" name="events[mouseOut]"><?php echo $fields['events']['mouseOut']?></textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label>beforeLoad</label>
                    <div class="controls">
                    <textarea class="input-xxlarge" rows="8" placeholder=""  name="events[beforeLoad]"><?php echo $fields['events']['beforeLoad']?></textarea>
                    </div>
                </div>

                <div class="control-group">
                    <label>afterLoad</label>
                    <div class="controls">
                    <textarea placeholder="" rows="8" class="input-xxlarge" name="events[afterLoad]"><?php echo $fields['events']['afterLoad']?></textarea>
                    </div>
                </div>
                

          </div>

        </div>


        <!-- SAVE -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" data-loading-text="Please wait..." id="btn-mapsvg-save">Save</button>
        </div>

    </form>


            <div class="modal" id="markModal" style="display: none">
              <div class="modal-header">
                <h3>Edit marker</h3>
              </div>
              <div class="modal-body">
                    <form class="form-horizontal" id="markForm" action="" method="post">
                        <div class="control-group">
                            <label>
                                Image
                            </label>
                            <div class="controls">
                                <select name="mark[attrs][src]" id="mark-attrs-src">
                                    <?php foreach($pin_files as $p){?>
                                        <option value="<?php echo MAPSVG_PINS_URL.$p?>"><?php echo $p?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                Tooltip
                            </label>
                            <div class="controls">
                                <textarea id="mark-tooltip" name="mark[data][tooltip]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                Popover
                            </label>
                            <div class="controls">
                                <textarea id="mark-popover" name="mark[data][popover]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL
                            </label>
                            <div class="controls">
                                <input type="text" id="mark-attrs-href" name="mark[attrs][href]"   placeholder="http://"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL Target
                            </label>
                            <div class="controls">
                                <input type="text" id="mark-attrs-target" name="mark[attrs][target]"   placeholder="blank"/>
                            </div>
                        </div>
                        <input type="hidden" id="markModalState" value="1"/>
                    </form>
              </div>
              <div class="modal-footer">
                <a href="#" id="markCancel" class="btn" data-dismiss="modal">Close</a>
                <a href="#" id="markDelete" class="btn btn-danger" data-dismiss="modal">Remove mark</a>
                <a href="#" id="markSave" class="btn btn-primary" data-dismiss="modal">Save changes</a>
              </div>
            </div>

            <!-- MODAL FOR REGIONS -->
            <div class="modal" id="markRegions" style="display: none;">
              <div class="modal-header">
                <h3>Edit region</h3>
              </div>
              <div class="modal-body">
                    <form class="form-horizontal" id="regionForm" action="" method="post">
                        <div class="control-group">
                            <label>
                                Color
                            </label>
                            <div class="controls">
                                <input type=""/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label>
                                Tooltip
                            </label>
                            <div class="controls">
                                <textarea id="region-tooltip" name="region[tooltip]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                Popover
                            </label>
                            <div class="controls">
                                <textarea id="region-popover" name="region[popover]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL
                            </label>
                            <div class="controls">
                                <input type="text" id="region-attrs-href" name="region[attrs][href]"   placeholder="http://"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL Target
                            </label>
                            <div class="controls">
                                <input type="text" id="region-attrs-target" name="region[attrs][target]"   placeholder="blank"/>
                            </div>
                        </div>
                        <input type="hidden" id="markModalState" value="1"/>
                    </form>
              </div>
              <div class="modal-footer">
                <a href="#" id="markCancel" class="btn" data-dismiss="modal">Close</a>
                <a href="#" id="markDelete" class="btn btn-danger" data-dismiss="modal">Remove mark</a>
                <a href="#" id="markSave" class="btn btn-primary" data-dismiss="modal">Save changes</a>
              </div>
            </div>


            <!-- MODAL FOR REGIONS -->
            <div class="modal" id="markRegions" style="display: none;">
              <div class="modal-header">
                <h3>Edit region</h3>
              </div>
              <div class="modal-body">
                    <form class="form-horizontal" id="regionForm" action="" method="post">
                        <div class="control-group">
                            <label>
                                Color
                            </label>
                            <div class="controls">
                                <input type=""/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label>
                                Tooltip
                            </label>
                            <div class="controls">
                                <textarea id="region-tooltip" name="region[tooltip]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                Popover
                            </label>
                            <div class="controls">
                                <textarea id="region-popover" name="region[popover]" placeholder="Any HTML content"></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL
                            </label>
                            <div class="controls">
                                <input type="text" id="region-attrs-href" name="region[attrs][href]"   placeholder="http://"/>
                            </div>
                        </div>
                        <div class="control-group">
                            <label>
                                URL Target
                            </label>
                            <div class="controls">
                                <input type="text" id="region-attrs-target" name="region[attrs][target]"   placeholder="blank"/>
                            </div>
                        </div>
                        <input type="hidden" id="markModalState" value="1"/>
                    </form>
              </div>
              <div class="modal-footer">
                <a href="#" id="markCancel" class="btn" data-dismiss="modal">Close</a>
                <a href="#" id="markDelete" class="btn btn-danger" data-dismiss="modal">Remove mark</a>
                <a href="#" id="markSave" class="btn btn-primary" data-dismiss="modal">Save changes</a>
              </div>
            </div>




            <?php if(!empty($import) && $import['type']=='marks'){?>
            <!-- MODAL FOR MARKS IMPORT -->
            <div class="modal" id="mapsvg-modal-import-marks" style="display: none;  overflow-x: hidden;">
              <div class="modal-header">
                <h3>Marks</h3>
              </div>
              <div class="modal-body">
                <div class="modal-body-table" style="display: none;">
                    
                    <div class="alert alert-info">
                        Please check the list of marks. If you'll find errors, click "Cancel", then edit your Excel file and import again. If everything is OK, scroll to the end of list and click "Save".
                    </div>
                    
                    <form class="" id="mapsvg-form-import-marks" action="" method="post">
                    
                        <table class="table table-striped" style="width: 950px;">
                            <thead>
                                <th style="width: 32px;"></th>
                                <th>Location</th>
                                <th>Tooltip</th>
                                <th>Popover</th>
                                <th>URL</th>
                                <th>URL Target</th>
                            </thead>
                            <tbody>
                                <?php $i = 0; ?>
                                <?php foreach($import['data'] as $m){?>
                                    <tr id="mapsvg-import-<?php echo $i;?>">    
                                        <td><img src="<?php echo MAPSVG_PLUGIN_URL.'markers/'.$m[0]; ?>" /><input type="hidden" name="marks[<?php echo $i;?>][attrs][src]" value="<?php echo MAPSVG_PLUGIN_URL.'markers/'.$m[0];?>"/></td>
                                        <td class="">
                                            <?php echo $m[1];?><br />
                                            <input type="hidden" name="marks[<?php echo $i;?>][c][0]" value=""/>
                                            <input type="hidden" name="marks[<?php echo $i;?>][c][1]" value=""/>                                    
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($m[3]);?>
                                            <textarea style="display: none;" name="marks[<?php echo $i;?>][tooltip]"><?php echo $m[3];?></textarea>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($m[4]);?>
                                            <textarea style="display: none;" name="marks[<?php echo $i;?>][popover]"><?php echo $m[4];?></textarea>
                                        </td>
                                        <td><?php echo $m[5];?><input type="hidden" name="marks[<?php echo $i;?>][attrs][href]" value="<?php echo $m[5];?>"/></td>
                                        <td><?php echo $m[6];?><input type="hidden" name="marks[<?php echo $i;?>][attrs][target]" value="<?php echo $m[6];?>"/></td>
                                        <input type="hidden" name="marks[<?php echo $i;?>][placed]" value="true"/>
                                        
                                                                                
                                    </tr>
                                <?php $i++; }?>
                            </tbody>
                        </table>
                        
                    </form>
                    </div>
                    <div class="modal-body-loading">
                        <div class="alert alert-info">
                            Please wait, fetching lat/lon coordinates from online API...
                        </div>
                        
                        <div class="progress progress-striped progress-success active">
                            <div class="bar" style="width: 5%;"></div>
                        </div>
                        
                    </div>
              </div>
              <div class="modal-footer" style="display: none;">
                <a href="#" class="btn btn-cancel" data-dismiss="modal">Cancel</a>
                <a href="#" class="btn btn-primary btn-save" data-dismiss="modal">Save</a>
              </div>
            </div>
            <?php }?>

            
            <!-- HIDDEN EXCEL IMPORT FORM -->
            <form id="form-import" method="post" enctype="multipart/form-data" style="position:  absolute; top: -9999px; left: -9999px;">
                <input type="hidden" name="import_objects" value=""/>
                <input type="hidden" name="import" value="1"/>
                <input type="file" id="mapsvg-choose-import-file" name="csv"/>
            </form>

    <?php endif;?>
    <?php }?>



</div>