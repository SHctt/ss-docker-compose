{include file='user/main.tpl'}
<main class="content">
    <div class="content-header ui-content-header">
        <div class="container">
            <h1 class="content-heading">虚空之地</h1>
        </div>
    </div>
    <div class="container">
        <section class="content-inner margin-top-no">
            <div class="ui-card-wrap">
                <div class="col-lg-12 col-md-12">
                    <section class="content-inner margin-top-no">
                        <div class="card">
                            <div class="card-main">
                                <div class="card-inner">
                                    <p>您由于以下原因而被管理员禁用了账户：</p>
                                    <p>{$user->disableReason()}</p>
                                    {if $config['enable_admin_contact'] == true}
                                        <p>如需帮助，请联系管理员：</p>
                                        {if $config['admin_contact1'] != ''}
                                            <li>{$config['admin_contact1']}</li>
                                        {/if}
                                        {if $config['admin_contact2'] != ''}
                                            <li>{$config['admin_contact2']}</li>
                                        {/if}
                                        {if $config['admin_contact3'] != ''}
                                            <li>{$config['admin_contact3']}</li>
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</main>

{include file='user/footer.tpl'}