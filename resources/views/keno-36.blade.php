<meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1" />

<meta http-equiv="X-UA-Compatible" content="IE=9" />

<!-- <link rel="stylesheet" href="{{ URL::asset("css/trend.css") }}"> -->
<link rel="stylesheet" href="https://yf-28.oss-cn-hongkong.aliyuncs.com/trend-chart/trend.css">
<title>{{$title}} - 走势图</title>
@if($request->iframe)
<style>
  body {
    background: white;
  }

  .content-wrap {
    box-shadow: none;
    padding: 0;
    margin: 0 0 !important;
  }

  .header-title {
    line-height: 20px;
    margin-bottom: 10px;
    border-bottom: 0 !important;
  }

  .select {
    top: 0 !important;
  }

  .table {
    min-width: 998px !important;

  }
</style>
@endif
<style>
  .table tr:nth-child(5n + {
        {
        $id_ass + 1
      }
    }

  ) td {
    border-bottom-color: #e4e6e7;
  }

  .table {
    min-width: 1000px;
  }

  .code-item {
    display: inline-block;
    margin-right: 10px;
  }
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
          <th colspan="3" class="br-dark">{{$limit}}期内标准次数</th>
          @foreach ($items["pro_stand"] as $val)
          <th>{{$val}}</th>
          @endforeach
        </tr>

        <tr>
          <th colspan=3 class="br-dark">{{$limit}}期内实际次数</th>
          @foreach ($items["pro_real"] as $val)
          <th>{{$val}}</th>
          @endforeach
        </tr>

        <tr>
          <th width="120" class="br-dark">期号</th>
          <th width="140" class="br-dark">时间</th>
          <th width="180" class="br-dark">开奖号码</th>
          <th width="100" class="br-dark">豹</th>
          <th width="100" class="br-dark">对</th>
          <th width="100" class="br-dark">顺</th>
          <th width="100" class="br-dark">半</th>
          <th width="100" class="br-dark">杂</th>
        </tr>
        </tr>
      </tbody>

      <tbody class="{{$request->chart}}">
        @foreach ($items["items"] as $key => $item)

        <tr>
          <td>{{$item["short_id"]}}</td>

          <td>{{$item["lotto_at"]}}</td>
          <td class="br-dark">
            @foreach($item["chart"]["code_arr"] as $code)
            <span class="code-item">{{$code}}</span>
            @endforeach
          </td>
          <td class="br-dark">@if($item["chart"]["code_ts"] === "ts_leo")<span class="extend-item red">豹</span>@endif</td>
          <td class="br-dark">@if($item["chart"]["code_ts"] === "ts_pai")<span class="extend-item green">对</span>@endif</td>
          <td class="br-dark">@if($item["chart"]["code_ts"] === "ts_jun")<span class="extend-item yellow">顺</span>@endif</td>
          <td class="br-dark">@if($item["chart"]["code_ts"] === "ts_juh")<span class="extend-item orange">半</span>@endif</td>
          <td class="br-dark">@if($item["chart"]["code_ts"] === "ts_oth")<span class="extend-item blue">杂</span>@endif</td>

        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


<script src="https://cdn.bootcss.com/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    var time = setInterval(() => {
      var tbody = document.body

      var height = tbody.scrollHeight

      window.parent.postMessage({
        height: height,

      }, '*')
      if (height > 0) {

        clearInterval(time);
      }
    }, 2000);
    $("#limit-select").change(function() {
      var v = $("#limit-select").val()
      var chart = "{{$request->chart}}"
      var name = "{{$request->name}}"
      var iframe = "{{$request->iframe}}"
      if (v == "") return false
      if (iframe) {
        window.location.href = "/trend-chart/" + name + "/" + chart + "/frame?limit=" + v;
      } else {
        window.location.href = "/trend-chart/" + name + "/" + chart + "?limit=" + v;
      }

    });
  });
</script>