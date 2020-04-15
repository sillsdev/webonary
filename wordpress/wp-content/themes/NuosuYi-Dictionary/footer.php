  <div id="ft" <?php if($qtransLang == "ii") {?>class="nuosu"<?php }?>>  
  	<?php wp_footer();  	
	if (function_exists('get_a_post')) { 
  		get_a_post('copyright');
  	?>  
    	<div style="font-size: 70%;">
    	&#169; <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> 
  	</div>
  	<?php 
	}
  	?>
  </div>
</div>
</body>
</html>
