# Bluestream Matrix Switcher HTTP-CGI commands:

## Definitions

| Word              | Description                                           | 
| -------------     |-------------                                          | 
| Switcher          | Bluestream Matrix Switcher                            | 
| XML               | Configration XML                                      |   
| Command           | Send a HTTP request to the HTTP-CGI                   |   
| {{paramater}}     | Replaceable values in the HTTP-CGI commands           |  

## A Note On Some Found Quirks

As I was reverse engineering the HTTP-CGI commands, I found that most things behave as excepted. Here are some
exceptions.

1) You will notice that integers switch between the use of padded (e.g. 01) and non-padded (e.g. 1). I noticed that you
   can send either but in the example URL's i have used what ever the original format was.

2) Some of the URL Commands contain spaces. I have left the spaces in the example and you will need to URL_ENCODE before
   sending commands to the HTTP-CGI.

3) Inputs and Outputs map to 1-based index. The number 0 as an index is supposed to be used as 'no input'. This however
   rarely works. For example, you would imagine that setting Output Number '1' to use the Input of '0' would blank that
   Output. It however, does nothing. If Output Number '1' was currently using Input '1', setting Input '0' would leave
   it at Input '1'. I have left the Commands to use the '0' as per my discovery.

## Get The Current Configration XML

`http://192.168.0.50/cgi-bin/getxml.cgi?xml=mxsta`

After running a Command, be sure to call the above for the most up to date version.

NOTE: Not sure if the Switcher can deliver other formats. Variations have been tried in the querystring (e.g. JSON) but
all give an empty response.

## INPUT: HDMI EDID Source Selections

`http://192.168.0.50/cgi-bin/submit?cmd=edid {{hdmi_input_channel}} df {{edid_value}}`

You can explicitly tell the Switcher what type of HDMI signal is being fed into the input channel. This can help in
situations where the Switcher cannot auto-detect the HDMI EDID information.

| Parameter             | Type          | Options                                               |
| -------------         |-------------  | -------------                                         |
| hdmi_input_channel    | integer       | 1 to N. N being the highest value in the XML          |
| edid_value            | integer       | See values below                                      |

**List of edid_values:**

```
label="HDMI-1080P Audio-2.0CH"      value="0"
label="HDMI-1080P Audio-5.1CH"      value="1"
label="HDMI-1080P Audio-7.1CH"      value="2"
label="HDMI-1080I Audio-2.0CH"      value="3"
label="HDMI-1080I Audio-5.1CH"      value="4"
label="HDMI-1080I Audio-7.1CH"      value="5"
label="HDMI-3D Audio-2.0CH"         value="6"
label="HDMI-3D Audio-5.1CH"         value="7"
label="HDMI-3D Audio-7.1CH"         value="8"
label="HDMI-4K2K Audio-2.0CH"       value="9"
label="HDMI-4K2K Audio-5.1CH"       value="10"
label="HDMI-4K2K Audio-7.1CH"       value="11"
label="DVI-1280x1024@60Hz"          value="12"
label="DVI-1920x1080@60Hz"          value="13"
label="DVI-1920x1200@60Hz"          value="14"
label="USER1 EDID"                  value="15"
label="USER2 EDID"                  value="16"
label="GUI DOWNLOAD EDID"           value="17"
label="HDMI-4K60 420 Audio-2.0CH"   value="18"
label="HDMI-4K60 420 Audio-5.1CH"   value="19"
label="HDMI-4K60 420 Audio-7.1CH"   value="20"
label="COPY OUTPUT 1 EDID"          value="48"
label="COPY OUTPUT 2 EDID"          value="49"
label="COPY OUTPUT 3 EDID"          value="50"
label="COPY OUTPUT 4 EDID"          value="51"
```

## INPUT: Create GUI Friendly Labels

`http://192.168.0.50/cgi-bin/submit?cfg:in{{hdmi_input_channel}}name={{label}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cfg:in1name=Mac Power Point`  
`http://192.168.0.50/cgi-bin/submit?cfg:in2name=Sony Camera`  
`http://192.168.0.50/cgi-bin/submit?cfg:in3name=Zoom Participants`

Helps to identify the input (and output) when creating a GUI for other purposes. Upon updating the input label, the
changes will be reflected in the XML.  
Note: The label will automatically be underscored upon submission to the HTTP-CGI.

| Parameter             | Type          | Options                                                                     |
| -------------         |-------------  | -------------                                                               |
| hdmi_input_channel    | integer       | 1 to N. N being the highest value in the XML                                |
| label                 | string (85)   | Spaces will be converted to underscore. Special characters limited to _~!@  |

## OUTPUT: Create GUI Friendly Labels

`http://192.168.0.50/cgi-bin/submit?cfg:out{{hdmi_output_channel}}name={{label}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cfg:out1name=Epson Projector`  
`http://192.168.0.50/cgi-bin/submit?cfg:out2name=Zoom Conference`  
`http://192.168.0.50/cgi-bin/submit?cfg:out3name=ATEM Mini Pro`  
`http://192.168.0.50/cgi-bin/submit?cfg:out4name=Preview Monitor`

Helps to identify the output (and input) when creating a GUI for other purposes. Upon updating the output label, the
changes will be reflected in the XML.  
Note: The label will automatically be underscored upon submission to the HTTP-CGI.

| Parameter             | Type          | Options                                                                       |
| -------------         |-------------  | -------------                                                                 |
| hdmi_output_channel   | integer       | 1 to N. N being the highest value in the XML                                  |
| label                 | string (85)   | Spaces will be converted to underscore. Special characters limited to _~!@    |

## OUTPUT: Enable or Disable HDMI Output

`http://192.168.0.50/cgi-bin/submit?cmd=out{{hdmi_output_channel}}{{status}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cmd=out01on`
`http://192.168.0.50/cgi-bin/submit?cmd=out01off`

Enable or disable the HDMI output channel.

| Parameter             | Type              | Options                                                           |
| -------------         |-------------      | -------------                                                     |
| hdmi_output_channel   | padded integer    | 01 to N. N being the highest value in the XML. Note the padding.  |
| status                | string            | Can only be `on` or `off`                                         |

## OUTPUT: Enable or Disable Power Over HDMI (POH)

`http://192.168.0.50/cgi-bin/submit?cmd=poh tx {{hdmi_output_channel}} {{status}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cmd=poh tx 01 off`
`http://192.168.0.50/cgi-bin/submit?cmd=poh tx 01 on`

The Switcher can deliver POH to supply the RX unit. If you disable POH, the RX unit will need its own power supply (i.e
wall-wart).

| Parameter             | Type              | Options                                                           |
| -------------         |-------------      | -------------                                                     |
| hdmi_output_channel   | padded integer    | 01 to N. N being the highest value in the XML. Note the padding.  |
| status                | string            | Can only be `on` or `off`                                         |

## PRESET: Configure Presets

`http://192.168.0.50/cgi-bin/submit?cfg:presets{{preset_number}}={{preset_matrix}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cfg:presets1=11110000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets2=12340000`

The Switcher can hold a set number of presets - check the XML for that number.

| Parameter     | Type              | Options                                                           |
| ------------- |-------------      | -------------                                                     |
| preset_number | integer           | 1 to N. N being the highest value in the XML.                     |
| preset_matrix | string(N)         | String of N digits. N being the highest value in the XML.         |

The following is the XML snippet from a 4x4 matrix Switcher. Note the Zeros (0) for the last 4 outputs. This XML snippet
would also be used for an 8x8 matrix Switcher. It is presumed that for a 16x16 matrix Switcher, you would need 16 values
in the comma separated values `<presets>` tag.

```xml
<!-- Extracted from http://192.168.0.50/cgi-bin/getxml.cgi?xml=mxsta -->
<MATRIX>
    <!-- abbreviated -->
    <presets>1,1,1,1,0,0,0,0</presets>
    <presets>1,2,3,4,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <!-- abbreviated -->
</MATRIX>
```

**The following 8 commands would set the XML Configuration to the above snippet**  
`http://192.168.0.50/cgi-bin/submit?cfg:presets1=11110000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets2=12340000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets3=00000000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets4=00000000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets5=00000000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets6=00000000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets7=00000000`  
`http://192.168.0.50/cgi-bin/submit?cfg:presets8=00000000`

**Preset Matrix Example**

|                  |     HDMI OUTPUT 1      |          ...           |          ...           |     HDMI OUTPUT N      | 
| ---------------- | :--------------------: | :--------------------: | :--------------------: | :--------------------: | 
| HDMI INPUT       | {{hdmi_input_channel}} | {{hdmi_input_channel}} | {{hdmi_input_channel}} | {{hdmi_input_channel}} |  

## SWITCHING: Set Output Destination To An Input Source

`http://192.168.0.50/cgi-bin/submit?cmd=out{{hdmi_output_channel}}fr{{hdmi_input_channel}}`

**Examples**  
`http://192.168.0.50/cgi-bin/submit?cmd=out01fr01`
`http://192.168.0.50/cgi-bin/submit?cmd=out04fr01`

After performing a switching, the changes will be reflected in then XML.

| Parameter             | Type              | Options                                                           |
| -------------         |-------------      | -------------                                                     |
| hdmi_output_channel   | padded integer    | 01 to N. N being the highest value in the XML. Note the padding.  |
| hdmi_input_channel    | padded integer    | 01 to N. N being the highest value in the XML. Note the padding.  |

## SWITCHING: Run A Preset

Despite the ability for the Switcher to hold presets, there is no command to run the defined presets. To run a preset do
the following:

1) Read the requested preset from the XML.
2) Generate and send the appropriate switching commands

**Read XML Example**  
User requests preset number 2.

```xml
<!-- Extracted from http://192.168.0.50/cgi-bin/getxml.cgi?xml=mxsta -->
<MATRIX>
    <!-- abbreviated -->
    <presets>1,1,1,1,0,0,0,0</presets>
    <presets>1,2,3,4,0,0,0,0</presets> <!-- user requests this preset -->
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <presets>0,0,0,0,0,0,0,0</presets>
    <!-- abbreviated -->
</MATRIX>
```

**Send HTTP-CGI Request Examples**  
Generate and send the following requests

```
http://192.168.0.50/cgi-bin/submit?cmd=out01fr01  
http://192.168.0.50/cgi-bin/submit?cmd=out02fr02  
http://192.168.0.50/cgi-bin/submit?cmd=out03fr03  
http://192.168.0.50/cgi-bin/submit?cmd=out04fr04  
http://192.168.0.50/cgi-bin/submit?cmd=out05fr00  
http://192.168.0.50/cgi-bin/submit?cmd=out06fr00  
http://192.168.0.50/cgi-bin/submit?cmd=out07fr00  
http://192.168.0.50/cgi-bin/submit?cmd=out08fr00  
```

After performing a switching, the changes will be reflected in then XML.



