
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Preview data</title>
</head>

<style>
table#tableList {
  border: 1px solid #eaeaea;
  width: 100%;
  border-collapse: collapse;
}

table#tableList tr th {
  border: 1px solid #eaeaea;
  padding: 5px 8px;
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
}

table#tableList tr td {
  border: 1px solid #eaeaea;
  padding: 2px 8px;
  font-family: 'Courier New', Courier, monospace;
  font-size: 12px;
}

.text-right{
    text-align: right;
}
.text-center{
    text-align: center;
}
.text-left{
    text-align: left;
}
</style>


<body>

  <h4 style="font-family: 'Courier New', Courier, monospace;">CV KUJANG MARINAS UTAMA
  </h4>

  <table style="width: 100%;border-collapse: collapse;font-size: 10px;">
    <tr>

      <td>
        <table
          style="width: 70%;border-collapse: collapse;font-size: 13px;font-family: 'Courier New', Courier, monospace;">
          <tr>
            <td>Salesman</td>
            <td>:</td>
            <td>{{$deliveryorder->getsales?$deliveryorder->getsales->nama:'-'}}</td>
          </tr>

          <tr>
            <td>Driver</td>
            <td>:</td>
            <td>{{$deliveryorder->getDriver?$deliveryorder->getDriver->nama:'-'}}</td>
          </tr>

        </table>
      </td>



      <td>
        <table
          style="width: 80%;border-collapse: collapse;font-size: 13px;font-family: 'Courier New', Courier, monospace;">
          <tr>
            <td>No. DO</td>
            <td>:</td>
            <td>{{$deliveryorder->no_do}}</td>
          </tr>
          <tr>
            <td>Tgl. DO</td>
            <td>:</td>
            <td>{{date("d/m/Y",strtotime($deliveryorder->tgl_do))}}</td>
          </tr>

        </table>
      </td>

    </tr>
  </table>

  <div style="margin-top: 20px;display: block;"></div>

  <table id="tableList">
    <thead>
      <tr>
        <th rowspan="2">PCODE</th>
        <th rowspan="2">Nama Barang</th>
        <th>Assembling</th>
        <th colspan="3">Total Assembling</th>
      </tr>
      <tr>
        <th>KRT. Utuh</th>
        <th>KRT</th>
        <th>LSN</th>
        <th>SAT</th>
      </tr>
    </thead>
    <tbody>
        {!! $detail[0] !!}
    </tbody>
  </table>

  <div style="margin-bottom: 10px;"></div>
  <table style="width: 100%;border-collapse: collapse;font-size: 13px;font-family: 'Courier New', Courier, monospace;">
    <tr>
      <td style="width: 150px;">JML KRT UTUH</td>
      <td style="width: 5px;">:</td>
      <td>{!! $detail[1] !!}</td>
    </tr>
    <tr>
      <td>JML KRT Assembling</td>
      <td>:</td>
      <td>{!! $detail[2] !!}</td>
    </tr>
    <tr>
      <td>Total KRT</td>
      <td>:</td>
      <td>{!! $detail[3] !!}</td>
    </tr>
  </table>
  <div style="margin-bottom: 10px;"></div>

  <table id="tableList">
    <thead>
      <tr>
        <th>Tgl. Faktur</th>
        <th>No. Faktur</th>
        <th>Outlet ID</th>
        <th>Nama Outlet</th>
        <th>Nilai Faktur</th>
      </tr>
    </thead>
    <tbody>
        {!! $detailfaktur[0] !!}
     <tr>
        <td colspan="4" style="text-align: right;">TOTAL</td>
        <td style="text-align: right;"> {!! $detailfaktur[1] !!}</td>
      </tr>
    </tbody>
  </table>
  <script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
  <script>
  $(document).ready(function() {

    window.onload = function() {
      window.print();
    }

    window.onmousemove = function() {
      window.close();
    }
  })
  </script>

</body>

</html>
