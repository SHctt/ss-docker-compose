{include file='user/main.tpl'}

<main class="content">
    <div class="content-header ui-content-header">
        <div class="container">
            <h1 class="content-heading">审计记录</h1>
        </div>
    </div>
    <div class="container">
        <div class="col-lg-12 col-md-12">
            <section class="content-inner margin-top-no">
                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                            <p>系统中所有审计记录。</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-main">
                        <div class="card-inner">
                            <div class="card-table">
                                <div class="table-responsive table-user">
                                    {$render}
                                    <table class="table">
                                        <tr>
                                            <th>ID</th>
                                            <th>节点ID</th>
                                            <th>节点名称</th>
                                            <th>规则ID</th>
                                            <th>名称</th>
                                            <th>描述</th>
                                            <th>正则表达式</th>
                                            <th>类型</th>
                                            <th>时间</th>
                                        </tr>
                                        {foreach $logs as $log}
                                            {assign var="rule" value=$log->rule()}
                                            {if $rule != null}
                                                <tr>
                                                    <td>#{$log->id}</td>
                                                    <td>{$log->node_id}</td>
                                                    <td>{$log->Node()->name}</td>
                                                    <td>{$log->list_id}</td>
                                                    <td>{$rule->name}</td>
                                                    <td>{$rule->text}</td>
                                                    <td>{$rule->regex}</td>
                                                    {if $rule->type == 1}
                                                        <td>数据包明文匹配</td>
                                                    {/if}
                                                    {if $rule->type == 2}
                                                        <td>数据包 hex 匹配</td>
                                                    {/if}
                                                    <td>{date('Y-m-d H:i:s',$log->datetime)}</td>
                                                </tr>
                                            {/if}
                                        {/foreach}
                                    </table>
                                    {$render}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</main>

{include file='user/footer.tpl'}
