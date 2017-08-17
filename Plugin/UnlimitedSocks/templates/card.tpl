<link rel="stylesheet" href="modules/servers/{$module}/templates/static/css/style.css">
<script src="modules/servers/{$module}/templates/static/js/Chart.js"></script>
<style>
.table-container
{
width: 100%;
overflow-y: auto;
_overflow: auto;
margin: 0 0 1em;
}

.table-container::-webkit-scrollbar
{
-webkit-appearance: none;
width: 14px;
height: 14px;
}

.table-container::-webkit-scrollbar-thumb
{
border-radius: 8px;
border: 3px solid #fff;
background-color: rgba(0, 0, 0, .3);
}

.table-container-outer { position: relative; }

.table-container-fade
{
	position: absolute;
	right: 0;
	width: 30px;
	height: 100%;
	background-image: -webkit-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -moz-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -ms-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: -o-linear-gradient(0deg, rgba(255,255,255,.5), #fff);
	background-image: linear-gradient(0deg, rgba(255,255,255,.5), #fff);
}
</style>
{if ($infos)}
	<div class="alert alert-success">
		<p>{$infos}</p>
	</div>
{/if}
{if ($errors)}
	<div class="alert alert-danger">
		<p>{$errors}</p>
	</div>
{/if}
<div class="plugin">
    <div class="row">
        <div class="col-md-12">
            <!--widget start-->
            <aside class="profile-nav alt">
                <section class="panel">
                    <ul class="nav nav-pills nav-stacked">
                        <form method="post" action="">
                            <li><h3 class="block-title text-primary">{get_lang('card_number')}</h3></li>
                            <li><input type="text" class="form-control" name="cardid" /required></li>
                            <li><input class="form-control" type="submit"/></li>
                        </form>
                    </ul>
                </section>
            </aside>
         </div>   
    </div>
</div>      