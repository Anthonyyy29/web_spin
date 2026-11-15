#Persistent
#InstallKeybdHook
#UseHook
#SingleInstance Force
SetBatchLines, -1

uid := ""
lastInputTime := 0

; Tangkap Enter (biasanya RFID kirim Enter di akhir)
~Enter::
if (uid != "")
{
    SendToServer(uid)
    uid := ""
}
return

; Tangkap karakter RFID (hex + angka)
~*a::
~*b::
~*c::
~*d::
~*e::
~*f::
~*0::
~*1::
~*2::
~*3::
~*4::
~*5::
~*6::
~*7::
~*8::
~*9::
uid .= SubStr(A_ThisHotkey, 3)
return


SendToServer(data)
{
    try
    {
        http := ComObjCreate("WinHttp.WinHttpRequest.5.1")
        
        http.Open("POST", "https://spintheradiofm.site/tap-absen", false)
        http.SetRequestHeader("Content-Type", "application/x-www-form-urlencoded")
        
        ; WAJIB untuk HTTPS
        http.Option(4) := 13056
        
        body := "rfid_uid=" data
        http.Send(body)

        if (http.Status = 200)
        {
            ; Bunyi sukses
            SoundBeep, 1000, 150
        }
        else
        {
            ; Bunyi gagal
            SoundBeep, 400, 400
        }
    }
    catch
    {
        ; Kalau error koneksi
        SoundBeep, 300, 500
    }
}