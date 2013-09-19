<?php include( 'inc/header.php' ); ?>
<?php include( '../includes/db.class.php' ); ?>

<?php

	$news = DB::get_news();

?>

<div id="index1">
	<table cellpadding="0" cellspacing="0" border="0">

		<tr class="top_content">

			<td class="left_column">

				<div id="advisors_image"><img src="img/indexpic.jpg" alt="" /></div>

			</td>

			<td class="right_column">

				<div id="facts_box"><p id="financial_fact"></p></div>

				<br />

				<ul>

					<li><a href="services.php">College Planning Information</a></li>

				</ul>

			</td>

		</tr>

		<tr class="bottom_content">

			<td class="left_column">

				<br />

				<p>"This is good stuff! I checked out the site a couple of times today and I am very impressed with the comprehensiveness. After I would sign out, I would think of another feature that would be cool, then sign back in to find out that it is already there. Very impressive. This could almost eliminate guidance counselors and college counselors."</p>

				<p>
					-- Michael R. (<em>New Jersey</em>) <br />
					Assistant Principal / Director of Guidance <br />
				</p>

			</td>

			<td class="right_column">

				<p>

					Collegiate Advisors, LLC <br />
					590 Centerville Road, #285<br />
					Lancaster, PA 17601

				</p>

				<hr class="small_separator" />

				<p>

					Phone: (888) 940-8394 <br />
					Facsimile: (888) 340-0501 <br />
					<a href="mailto:answers@collegiateadvisors.com">answers@collegiateadvisors.com</a>

				</p>

				<!--<hr class="small_separator" />

				<div class="oneeighthundred">

					<h3>Free Student Loan Advice<br />(800) 515-3807</h3>

					<p>Find out which loans are best for you!</p>

				</div>-->

			</td>

		</tr>

	</table>

	<div class="containing_box">

		<div class="header">News</div>

		<?php foreach( $news as $news_item ): ?>

			<h3><?php echo date( 'F j, Y', $news_item['date'] ); ?></h3>
			<div class="news_item">

				<p><?php echo $news_item['news']; ?></p>

			</div>

			<div class="option">(<a href="<?php echo $news_item['link']; ?>" target="_blank"><?php echo $news_item['link_description']; ?></a>)</div>
			<br style="clear: right;" />

		<?php endforeach; ?>

	</div>

</div>

<script type="text/javascript" src="js/index.js"></script>


<?php include( 'inc/footer.php' );?>