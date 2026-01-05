// DataTables Initialization

$(document).ready(function() {
    // Barcha myTable_ prefiksi bilan boshlanadigan tablelarni initialize qilish
    $('table[id^="myTable_"]').each(function() {
        $(this).DataTable({
            ordering: true,
            order: [[0, 'asc']],
            paging: false,
            lengthChange: false,
            language: {
                search: "Qidiruv:",
                zeroRecords: "Hech qanday mos yozuv topilmadi",
            },
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf', 'print'],
            info: false
        });
    });
    
    // myTable2 ni alohida initialize qilish
    if ($('#myTable2').length) {
        $('#myTable2').DataTable({
            ordering: true,
            order: [[0, 'asc']],
            paging: true,
            pageLength: 10,
            lengthChange: false,
            language: {
                search: "Qidiruv:",
                info: "_TOTAL_ ta yozuvdan _START_ dan _END_ gacha ko'rsatilmoqda",
                zeroRecords: "Hech qanday mos yozuv topilmadi",
            },
            dom: 'Bfrtip',
            buttons: ['excel', 'pdf', 'print']
        });
        
        // Font size o'zgartirish
        $('#myTable2 td, #myTable2 th').css('font-size', '15px');
    }
});