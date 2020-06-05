<meta http-equiv='X-UA-Compatible' content='IE=edge,Chrome=1' />

<meta http-equiv='X-UA-Compatible' content='IE=9' />

<link rel="stylesheet" href="{{ URL::asset('css/trend.css') }}">
<title>幸运28--走势图</title>
<div class='trend'>
  <div class='Content'>
  </div>
  <div class='table'>
    <table class='table_list' cellspacing='1' cellpadding='0'>
      <tbody>
        <tr bgcolor='#fbfbfb'>
          <th colspan='41' style='height: 30px;line-height: 30px;'>
            <div style='position: relative;'>幸运28走势图 <select id='sltNum'
                style='margin: 5px 10px;position: absolute;right: 0;'>
                <option value=100 @if(request()->limit == 100) selected='selected' @endif>最新100期</option>
                <option value=200 @if(request()->limit == 200) selected='selected' @endif>最新200期</option>
                <option value=300 @if(request()->limit == 300) selected='selected' @endif>最新300期</option>
                <option value=400 @if(request()->limit == 400) selected='selected' @endif>最新400期</option>
                <option value=500 @if(request()->limit == 500) selected='selected' @endif>最新500期</option>
                <option value=600 @if(request()->limit == 600) selected='selected' @endif>最新600期</option>
                <option value=700 @if(request()->limit == 700) selected='selected' @endif>最新700期</option>
                <option value=800 @if(request()->limit == 800) selected='selected' @endif>最新800期</option>
                <option value=900 @if(request()->limit == 900) selected='selected' @endif>最新900期</option>
                <option value=1000 @if(request()->limit == 1000) selected='selected' @endif>最新1000期</option>
              </select></div>
          </th>
        </tr>
        <tr class='timeh'>
          <th colspan='2'><b class='black777'>两期标准间隔</b></th>
          <th>1000</th>
          <th>333</th>
          <th>167</th>
          <th>100</th>
          <th>67</th>
          <th>48</th>
          <th>36</th>
          <th>28</th>
          <th>22</th>
          <th>18</th>
          <th>16</th>
          <th>14</th>
          <th>14</th>
          <th>13</th>
          <th>13</th>
          <th>14</th>
          <th>14</th>
          <th>16</th>
          <th>18</th>
          <th>22</th>
          <th>28</th>
          <th>36</th>
          <th>48</th>
          <th>67</th>
          <th>100</th>
          <th>167</th>
          <th>333</th>
          <th>1000</th>
          <th colspan='11' style='background: white;'>* <span style='color:#5D5D5D'> 注:两个相同开奖结果之间标准间隔期数（概率计算）</span>
          </th>
        </tr>
        <tr class='timeh'>
          <th colspan='2'><b class='black777'>两期实际间隔</b></th>
          <th>1436</th>
          <th>25</th>
          <th>3</th>
          <th>50</th>
          <th>26</th>
          <th>0</th>
          <th>32</th>
          <th>31</th>
          <th>4</th>
          <th>11</th>
          <th>12</th>
          <th>16</th>
          <th>1</th>
          <th>18</th>
          <th>14</th>
          <th>2</th>
          <th>8</th>
          <th>84</th>
          <th>20</th>
          <th>36</th>
          <th>44</th>
          <th>59</th>
          <th>195</th>
          <th>29</th>
          <th>85</th>
          <th>162</th>
          <th>1713</th>
          <th>216</th>
          <th colspan='11' style='background: white;'>* <span style='color:#5D5D5D'> 注:若有存在空白位，即网站暂时没有该开奖结果记录</span>
          </th>
        </tr>
        <tr class='timeh'>
          <th colspan='2'><b class='black777'>100期内标准次数</b></th>
          <th>0</th>
          <th>0</th>
          <th>0</th>
          <th>1</th>
          <th>1</th>
          <th>2</th>
          <th>2</th>
          <th>3</th>
          <th>4</th>
          <th>5</th>
          <th>6</th>
          <th>6</th>
          <th>7</th>
          <th>7</th>
          <th>7</th>
          <th>7</th>
          <th>6</th>
          <th>6</th>
          <th>5</th>
          <th>4</th>
          <th>3</th>
          <th>2</th>
          <th>2</th>
          <th>1</th>
          <th>1</th>
          <th>0</th>
          <th>0</th>
          <th>0</th>
          <th>50</th>
          <th>50</th>
          <th>56</th>
          <th>44</th>
          <th>50</th>
          <th>50</th>
          <th colspan='5' style='background: white;'>* <span style='color:#5D5D5D'>注:<span
                style='color:red;font-weight: bold;'>100</span> 期开奖次数</span></th>
        </tr>
        <tr class='timeh'>
          <th colspan=2><b class='black777'>100期内实际次数</b></th>
          <th>0</th>
          <th>1</th>
          <th>1</th>
          <th>1</th>
          <th>1</th>
          <th>3</th>
          <th>5</th>
          <th>5</th>
          <th>6</th>
          <th>6</th>
          <th>7</th>
          <th>6</th>
          <th>4</th>
          <th>4</th>
          <th>9</th>
          <th>19</th>
          <th>7</th>
          <th>1</th>
          <th>4</th>
          <th>3</th>
          <th>2</th>
          <th>3</th>
          <th>0</th>
          <th>1</th>
          <th>1</th>
          <th>0</th>
          <th>0</th>
          <th>0</th>
          <th>53</th>
          <th>47</th>
          <th>57</th>
          <th>43</th>
          <th>50</th>
          <th>50</th>
          <th colspan=2><b class='black777'>尾数</b></th>
          <th colspan=3><b class='black777'>余数</b></th>
        </tr>
        <tr class='font_color_2' bgcolor='#e3f0ff'>
          <th width='50'>期号</th>
          <th width='60'>时间</th>
          <th width='22'>0</th>
          <th width='22'>1</th>
          <th width='22'>2</th>
          <th width='22'>3</th>
          <th width='22'>4</th>
          <th width='22'>5</th>
          <th width='22'>6</th>
          <th width='22'>7</th>
          <th width='22'>8</th>
          <th width='22'>9</th>
          <th width='22'>10</th>
          <th width='22'>11</th>
          <th width='22'>12</th>
          <th width='22'>13</th>
          <th width='22'>14</th>
          <th width='22'>15</th>
          <th width='22'>16</th>
          <th width='22'>17</th>
          <th width='22'>18</th>
          <th width='22'>19</th>
          <th width='22'>20</th>
          <th width='22'>21</th>
          <th width='22'>22</th>
          <th width='22'>23</th>
          <th width='22'>24</th>
          <th width='22'>25</th>
          <th width='22'>26</th>
          <th width='22'>27</th>
          <th width='22'>单</th>
          <th width='22'>双</th>
          <th width='22'>中</th>
          <th width='22'>边</th>
          <th width='22'>大</th>
          <th width='22'>小</th>
          <th width='22'>大</th>
          <th width='22'>小</th>
          <th width='22'>3/</th>
          <th width='22'>4/</th>
          <th width='22'>5/</th>
        </tr>
        </tr>
      </tbody>
      <tbody>
        @foreach ($items as $key => $item)

        <tr>
          <td class='tdbg3'>{{$item['id']}}</td>
          <td class='black777'>{{$item['lotto_at']}}</td>
          @foreach ($code_array as $code)
          <td @if($code<=9 || $code>=18)class='bgnum' @endif>@if($item['chart']['win_he'] == $code)<em
              class='final'><i>{{$code}}</i></em>@endif</td>
          @endforeach

          @if($item['chart']['single']) <td class='bgkai01'>单</td> @else <td></td> @endif
          @if($item['chart']['double']) <td class='bgkai02'>双</td> @else <td></td> @endif
          @if($item['chart']['double']) <td class='bgkai03'>中</td> @else <td></td> @endif
          @if($item['chart']['bian']) <td class='bgkai04'>边</td> @else <td></td> @endif
          @if($item['chart']['big']) <td class='bgkai05'>大</td> @else <td></td> @endif
          @if($item['chart']['small']) <td class='bgkai06'>小</td> @else <td></td> @endif

          @if($item['chart']['wei_big']) <td class='bgkai07'>大</td> @else <td></td> @endif
          @if($item['chart']['wei_small']) <td class='bgkai08'>小</td> @else <td></td> @endif
          <td class='black333'>{{$item['chart']['yu_3']}}</td>
          <td class='black333'>{{$item['chart']['yu_4']}}</td>
          <td class='black333'>{{$item['chart']['yu_5']}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<script src="{{ URL::asset('js/jquery.min.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('#sltNum').change(function () {
      var v = $('#sltNum').val();
      var baseUrl = "{{URL::to('/')}}";
      if (v != '') {
        window.location.href = baseUrl+'/api/lotto/chart?name=ca28&limit=' + v;
      }
    });
  });
</script>