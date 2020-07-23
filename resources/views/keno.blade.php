<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />

<meta http-equiv="X-UA-Compatible" content="IE=9" />

<!-- <link rel="stylesheet" href="{{ URL::asset("css/trend.css") }}"> -->
<link rel="stylesheet" href="https://yf-28.oss-cn-hongkong.aliyuncs.com/trend-chart/trend.css">
<title>{{$title}} - 走势图</title>

<style>
  .table tr:nth-child(5n + {{$id_ass + 1}}) td{border-bottom-color: #e4e6e7;}
</style>

<div class="content-wrap">
  <div class="header-title">
    <span>{{$title}} - 走势图</span>
    <select id="limit-select" class="select">
      <option value=100 @if($request->limit == 100) selected="selected" @endif>最新100期</option>
      <option value=200 @if($request->limit == 200) selected="selected" @endif>最新200期</option>
      <option value=300 @if($request->limit == 300) selected="selected" @endif>最新300期</option>
      <option value=400 @if($request->limit == 400) selected="selected" @endif>最新400期</option>
      <option value=500 @if($request->limit == 500) selected="selected" @endif>最新500期</option>
      <option value=600 @if($request->limit == 600) selected="selected" @endif>最新600期</option>
      <option value=700 @if($request->limit == 700) selected="selected" @endif>最新700期</option>
      <option value=800 @if($request->limit == 800) selected="selected" @endif>最新800期</option>
      <option value=900 @if($request->limit == 900) selected="selected" @endif>最新900期</option>
      <option value=1000 @if($request->limit == 1000) selected="selected" @endif>最新1000期</option>
    </select>
  </div>

  <div class="table-wrap">
    <table class="table" cellspacing="1" cellpadding="0">
      <tbody class="{{$request->chart}}">
        <tr>
          <th colspan="2" class="br-dark">{{$limit}}期内标准次数</th>
          @foreach ($items["pro_stand"] as $val)
          <th>{{$val}}</th>
          @endforeach
          <th colspan="5">注:{{$limit}}期开奖次数</th>
        </tr>

        <tr>
          <th colspan=2 class="br-dark">{{$limit}}期内实际次数</th>
          @foreach ($items["pro_real"] as $val)
          <th>{{$val}}</th>
          @endforeach
          <th colspan=2 class="br-dark">尾数</th>
          <th colspan=3>余数</th>
        </tr>

        <tr>
          <th width="70" class="br-dark">期号</th>
          <th width="110" class="br-dark">时间</th>
          @foreach ($items["code_place"] as $code)
          <th width="28">{{$code}}</th>
          @endforeach

          <th width="28">大</th>
          <th width="28">小</th>
          <th width="28">单</th>
          <th width="28">双</th>
          <th width="28">中</th>
          <th width="28" class="br-dark">边</th>
          <th width="28">大</th>
          <th width="28" class="br-dark">小</th>
          <th width="28">3/</th>
          <th width="28">4/</th>
          <th width="28">5/</th>
        </tr>
        </tr>
      </tbody>

      <tbody class="{{$request->chart}}">
        @foreach ($items["items"] as $key => $item)

        <tr>
          <td>{{$item["short_id"]}}</td>

          <td>{{$item["lotto_at"]}}</td>

          @foreach ($items["code_place"] as $code)
          <td>@if($item["chart"]["win_he"] == $code)<span class="code-item">{{$code}}</span>@endif</td>
          @endforeach

          <td>@if($item["chart"]["big"])<span class="extend-item blue">大</span>@endif</td>
          <td>@if($item["chart"]["small"])<span class="extend-item yellow">小</span>@endif</td>
          <td>@if($item["chart"]["single"])<span class="extend-item green">单</span>@endif</td>
          <td>@if($item["chart"]["double"])<span class="extend-item red">双</span>@endif</td>
          <td>@if($item["chart"]["middle"])<span class="extend-item orange">中</span>@endif</td>
          <td>@if($item["chart"]["side"])<span class="extend-item pink">边</span>@endif</td>
          <td>@if($item["chart"]["mta_big"])<span class="extend-item green">大</span>@endif</td>
          <td>@if($item["chart"]["mta_small"])<span class="extend-item yellow">小</span>@endif</td>


          <td>{{$item["chart"]["mod_3"]}}</td>
          <td>{{$item["chart"]["mod_4"]}}</td>
          <td>{{$item["chart"]["mod_5"]}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $("#limit-select").change(function () {
      var v = $("#limit-select").val()
      var chart = "{{$request->chart}}"
      var name = "{{$request->name}}"
      if (v == "") return false
      window.location.href = "/trend-chart/"+name+"/"+chart+"?limit=" + v;

    });
  });
</script>
