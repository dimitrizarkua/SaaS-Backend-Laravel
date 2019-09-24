<tr style="border-top: 1px solid #D3DBDE;background: #FAFAFA;">
    <td style="padding: 15px 15px 15px 15px;">{{$item['description']}}</td>
    <td style="padding: 15px 15px 15px 15px;">{{$item['qty']}}</td>
    <td style="text-align: right;padding: 15px 15px 15px 15px;">$ {{number_format($item['item_amount'], 2, '.', ',')}}</td>
    <td style="padding: 15px 15px 15px 15px;">{{$item['tax_rate']}}</td>
    <td style="text-align: right;padding: 15px;">$ {{number_format($item['total_amount'], 2, '.',',')}}</td>
</tr>