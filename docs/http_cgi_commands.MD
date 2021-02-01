# Bluestream Matrix Switcher HTTP-CGI commands:

Reverse engineered from the Web GUI that is included with Bluestream products.

## Definitions

| Word              | Description                                           | 
| -------------     |-------------                                          | 
| Switcher          | Bluestream Matrix Switcher                            | 
| XML               | Configration XML                                      |   
| Command           | Send a HTTP request to the HTTP-CGI                   |   
| {{paramater}}     | Replaceable values in the HTTP-CGI commands           |  
| Source            | Physical source such as computer or DVD player        |  
| Input Channel     | Physical port on the Switcher (e.g. HDMI input)       |  
| Output Channel    | Physical port on the Switcher (e.g. HDMI output)      |  
| Display           | Physical display such as monitor or TV                |  

## A Note On Signal Flow In Matrix Switchers

In case you are unfamiliar with Matrix Switchers, HDMI signals and EDID information flow in **_opposite_** directions...

**Example HDMI Signl Flow (Left to Right):**

```
- Source A is routed to Displays A,B & C 
- Source B goes nowhere
- Source C goes nowhere
- Source D is routed to Display D

             |-------------Matrix Switcher------------|
             |                                        |  
Source A ->  | Input Channel 1 --|-> Output Channel 2 | -> Display A (native 1080p display)
             |                   |                    |  
Source B ->  | Input Channel 2   |-> Output Channel 2 | -> Display B (native 4K display)
             |                   |                    |  
Source C ->  | Input Channel 3   |-> Output Channel 3 | -> Display C (native 1920x1200px display)
             |                                        |  
Source D ->  | Input Channel 4  ---> Output Channel 4 | -> Display D (native 1080p display)
             |                                        |  
             |----------------------------------------|
```

**Example EDID Signl Flow (Right to Left):**

```
- Source A could be receiving conflicting EDID inforation from Displays A,B & C 
- Source B no EDID information received
- Source C no EDID information received
- Source D receives EDID information from Display D

             |-------------Matrix Switcher------------|
             |                                        |  
Source A <-  | Input Channel 1 <-|-- Output Channel 2 | <- Display A (native 1080p display)
             |                   |                    |  
Source B <-  | Input Channel 2   |-- Output Channel 2 | <- Display B (native 4K display)
             |                   |                    |  
Source C <-  | Input Channel 3   |-- Output Channel 3 | <- Display C (native 1920x1200px display)
             |                                        |  
Source D <-  | Input Channel 4  <--- Output Channel 4 | <- Display D (native 1080p display)
             |                                        |  
             |----------------------------------------|
```

In the scenario above, what format should 'Source A' be sending - 1080p, 4K or 1920x1200px?  
See section **'INPUT: HDMI EDID Source Selection'** to force a specific EDID on an Input Channel.

## A Note On Some Found Quirks

As I was reverse engineering the HTTP-CGI commands, I found that most things behave as excepted. Here are some
exceptions.

1) You will notice that integers switch between the use of padded (e.g. 01) and non-padded (e.g. 1). I noticed that you
   can send either but in the example URL's I have used whatever the original format was.

2) Some URL Commands contain spaces. I have left the spaces in the examples. You will need to `urlencode()` before
   sending commands to the HTTP-CGI.

3) Inputs and Outputs use a 1-based index. The value 0 as an index is supposed to be used as 'no input'. This however
   rarely works. For example, you would imagine that setting Output Number '1' to use the Input of '0' would blank that
   Output. It however, does nothing. If Output Number '1' was currently using Input '1', setting it to Input '0' would
   leave it at Input '1'. I have left the Commands to use the '0' as per my discovery.

## Get The Current Configration XML

`http://192.168.0.50/cgi-bin/getxml.cgi?xml=mxsta`

After running a Command, be sure to call the above for the most up to date version.

The XML is not particularly well nested as one would expect. The root tag is `<MATRIX></MATRIX>` and you will often
find...

```xml

<MATRIX>
    <foo>val</foo>
    <foo>val</foo>
    <bar>val</bar>
    <bar>val</bar>
</MATRIX>
```

as opposed to...

```xml

<MATRIX>
    <foos>
        <foo>val</foo>
        <foo>val</foo>
    </foos>
    <bars>
        <bar>val</bar>
        <bar>val</bar>
    </bars>
</MATRIX>
```

Only a minor inconvenience to most programming languages. See `/docs/http_cgi_example_xml_01.xml` for an example.
https://github.com/arajcany/BluestreamMatrixController/blob/main/docs/http_cgi_example_xml_01.xml

NOTE: Not sure if the Switcher can deliver other formats. Variations have been tried in the querystring (e.g. JSON) but
all give an empty response.

## INPUT: HDMI EDID Source Selection

`http://192.168.0.50/cgi-bin/submit?cmd=edid {{hdmi_input_channel}} df {{edid_value}}`

You can explicitly tell the Switcher what type of HDMI format (via EDID information) should being present on the Input
Channel that will be fed back down to the Source. This can help in situations where the Switcher cannot auto-detect the
HDMI EDID information that the Display is presenting to the Output Channel or when you have Displays with varying
capabilities.

| Parameter             | Type          | Options                                               |
| -------------         |-------------  | -------------                                         |
| hdmi_input_channel    | integer       | 1 to N. N being the highest value in the XML.         |
| edid_value            | integer       | See values below                                      |

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
Note: The label will be underscored automatically upon submission of the Command.

| Parameter             | Type          | Options                                                                     |
| -------------         |-------------  | -------------                                                               |
| hdmi_input_channel    | integer       | 1 to N. N being the highest value in the XML.                               |
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
Note: The label will be underscored automatically upon submission of the Command.

| Parameter             | Type          | Options                                                                       |
| -------------         |-------------  | -------------                                                                 |
| hdmi_output_channel   | integer       | 1 to N. N being the highest value in the XML.                                 |
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

## OUTPUT: Enable or Disable Power Over HDBaseT™ (POH)

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
2) Generate and send the appropriate switching Commands

**Read XML Example**  
If the user requests Preset Number 2...

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
Generate and send the following Commands...

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



