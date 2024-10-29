<div id="<?php echo "adk-widget-{$name}"; ?>" class="adk-widget-wrapper" style="width: 100%; position: absolute; top: 0; z-index: 10000;">
	<div style="width:<?php echo $width; ?>px;
				max-width: 100%;
				margin: 0 auto;
				border-radius: <?php echo $border_radius; ?>px;
				border: solid <?php echo "${border_width}px $border_color"; ?>;
				box-shadow: <?php echo "${shadow_vertical}px ${shadow_horizontal}px ${shadow_dispersion}px $shadow_color"; ?>;
				overflow: hidden;
				position: relative;"
	>
		<div style="position: absolute;
					top: 10px;
					right: 10px;
					padding: 3px;
					line-height: 21px;
					border: solid 2px white;
					border-radius: 15px;
					cursor: pointer;
					font-size: 35px;
					font-weight: bold;
					color: white;
					background-color: red;"
			data-for="<?php echo "#adk-widget-{$name}"; ?>"
			class="adk-widget-close"
		><div>&times;</div></div>
		<table style="border-collapse: collapse;
					margin: 0;"
		>
			<tr>
				<?php echo $content; ?>
			</tr>
		</table>
	</div>
</div>