// Decompiled by Jad v1.5.8e2. Copyright 2001 Pavel Kouznetsov.
// Jad home page: http://kpdus.tripod.com/jad.html
// Decompiler options: packimports(3) fieldsfirst ansi space 
// Source File Name:   DES_Algorithm.java


import java.io.PrintStream;
import java.io.PrintWriter;
import java.security.InvalidKeyException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.text.ParseException;

public final class DES_Algorithm
{

	static final String NAME = "DES_Algorithm";
	static final boolean IN = true;
	static final boolean OUT = false;
	static final boolean DEBUG = false;
	static final int debuglevel = 0;
	static final PrintWriter err = null;
	static final boolean TRACE = false;
	private static final int ROUNDS = 16;
	private static final int BLOCK_SIZE = 8;
	private static final int SKB[];
	private static final int SP_TRANS[];
	private static final char HEX_DIGITS[] = {
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 
		'A', 'B', 'C', 'D', 'E', 'F'
	};

	public DES_Algorithm()
	{
	}

	protected static final boolean areEqual(byte a[], byte b[])
	{
		int aLength = a.length;
		if (aLength != b.length)
			return false;
		for (int i = 0; i < aLength; i++)
			if (a[i] != b[i])
				return false;

		return true;
	}

	protected static byte[] blockDecrypt(byte in[], int inOffset, Object sessionKey)
	{
		int L_R[] = {
			in[inOffset++] & 0xff | (in[inOffset++] & 0xff) << 8 | (in[inOffset++] & 0xff) << 16 | (in[inOffset++] & 0xff) << 24, in[inOffset++] & 0xff | (in[inOffset++] & 0xff) << 8 | (in[inOffset++] & 0xff) << 16 | (in[inOffset] & 0xff) << 24
		};
		IP(L_R);
		decrypt(L_R, sessionKey);
		FP(L_R);
		int L = L_R[0];
		int R = L_R[1];
		byte result[] = {
			(byte)L, (byte)(L >> 8), (byte)(L >> 16), (byte)(L >> 24), (byte)R, (byte)(R >> 8), (byte)(R >> 16), (byte)(R >> 24)
		};
		return result;
	}

	protected static byte[] blockEncrypt(byte in[], int inOffset, Object sessionKey)
	{
		int L_R[] = {
			in[inOffset++] & 0xff | (in[inOffset++] & 0xff) << 8 | (in[inOffset++] & 0xff) << 16 | (in[inOffset++] & 0xff) << 24, in[inOffset++] & 0xff | (in[inOffset++] & 0xff) << 8 | (in[inOffset++] & 0xff) << 16 | (in[inOffset] & 0xff) << 24
		};
		IP(L_R);
		encrypt(L_R, sessionKey);
		FP(L_R);
		int L = L_R[0];
		int R = L_R[1];
		byte result[] = {
			(byte)L, (byte)(L >> 8), (byte)(L >> 16), (byte)(L >> 24), (byte)R, (byte)(R >> 8), (byte)(R >> 16), (byte)(R >> 24)
		};
		return result;
	}

	protected static int[] crypt3(int L0, int R0, Object sessionKey)
	{
		int sKey[] = (int[])sessionKey;
		int L = 0;
		int R = 0;
		int n = sKey.length;
		for (int i = 0; i < 25; i++)
		{
			int t;
			for (int j = 0; j < n;)
			{
				int v = R ^ R >>> 16;
				int u = v & L0;
				v &= R0;
				u ^= u << 16 ^ R ^ sKey[j++];
				t = v ^ v << 16 ^ R ^ sKey[j++];
				t = t >>> 4 | t << 28;
				L ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
				v = L ^ L >>> 16;
				u = v & L0;
				v &= R0;
				u ^= u << 16 ^ L ^ sKey[j++];
				t = v ^ v << 16 ^ L ^ sKey[j++];
				t = t >>> 4 | t << 28;
				R ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
			}

			t = L;
			L = R;
			R = t;
		}

		int result[] = {
			R >>> 1 | R << 31, L >>> 1 | L << 31
		};
		FP(result);
		return result;
	}

	static void debug(String s)
	{
		err.println((new StringBuilder(">>> DES_Algorithm: ")).append(s).toString());
	}

	protected static final void decrypt(byte io[], Object sessionKey)
	{
		int L_R[] = {
			io[0] & 0xff | (io[1] & 0xff) << 8 | (io[2] & 0xff) << 16 | (io[3] & 0xff) << 24, io[4] & 0xff | (io[5] & 0xff) << 8 | (io[6] & 0xff) << 16 | (io[7] & 0xff) << 24
		};
		decrypt(L_R, sessionKey);
		int L = L_R[0];
		int R = L_R[1];
		io[0] = (byte)L;
		io[1] = (byte)(L >> 8);
		io[2] = (byte)(L >> 16);
		io[3] = (byte)(L >> 24);
		io[4] = (byte)R;
		io[5] = (byte)(R >> 8);
		io[6] = (byte)(R >> 16);
		io[7] = (byte)(R >> 24);
	}

	protected static final void decrypt(int io[], Object sessionKey)
	{
		int sKey[] = (int[])sessionKey;
		int L = io[0];
		int R = io[1];
		int u = R << 1 | R >>> 31;
		R = L << 1 | L >>> 31;
		L = u;
		int n = sKey.length;
		for (int i = n - 1; i > 0;)
		{
			int t = R ^ sKey[i--];
			u = R ^ sKey[i--];
			t = t >>> 4 | t << 28;
			L ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
			t = L ^ sKey[i--];
			u = L ^ sKey[i--];
			t = t >>> 4 | t << 28;
			R ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
		}

		io[0] = L >>> 1 | L << 31;
		io[1] = R >>> 1 | R << 31;
	}

	public void doit()
	{
		boolean ok = false;
		int BLOCK_SIZE = 8;
		try
		{
			byte kb[] = new byte[BLOCK_SIZE];
			byte pt[] = new byte[BLOCK_SIZE];
			for (int i = 0; i < BLOCK_SIZE; i++)
				kb[i] = (byte)i;

			for (int i = 0; i < BLOCK_SIZE; i++)
				pt[i] = (byte)i;

			Object key = makeKey(kb);
			byte ct[] = blockEncrypt(pt, 0, key);
			byte tmp[] = blockDecrypt(ct, 0, key);
			System.out.println((new StringBuilder("       key: ")).append(toString(kb)).toString());
			System.out.println((new StringBuilder(" plaintext: ")).append(toString(pt)).toString());
			System.out.println((new StringBuilder("ciphertext: ")).append(toString(ct)).toString());
			System.out.println((new StringBuilder("  computed: ")).append(toString(tmp)).toString());
			String kat[][] = {
				{
					"0101010101010101", "95f8a5e5dd31d900", "8000000000000000"
				}, {
					"0101010101010101", "dd7f121ca5015619", "4000000000000000"
				}, {
					"0101010101010101", "2e8653104f3834ea", "2000000000000000"
				}, {
					"0123456789abcdef", "0123456789abcde7", "c95744256a5ed31d"
				}, {
					"0123456710325476", "89abcdef98badcfe", "f0148eff050b2716"
				}
			};
			for (int i = 0; i < kat.length;)
			{
				key = makeKey(fromString(kat[i][0]));
				pt = fromString(kat[i][1]);
				ct = fromString(kat[i][2]);
				i++;
				tmp = blockEncrypt(pt, 0, key);
				System.out.println((new StringBuilder("KAT triple #")).append(i).toString());
				System.out.println((new StringBuilder("       key: ")).append(toString(kb)).toString());
				System.out.println((new StringBuilder(" plaintext: ")).append(toString(pt)).toString());
				System.out.println((new StringBuilder("ciphertext: ")).append(toString(ct)).toString());
				System.out.println((new StringBuilder("  computed: ")).append(toString(tmp)).toString());
				tmp = blockDecrypt(ct, 0, key);
				System.out.println((new StringBuilder("KAT triple #")).append(i).toString());
				System.out.println((new StringBuilder("       key: ")).append(toString(kb)).toString());
				System.out.println((new StringBuilder("ciphertext: ")).append(toString(ct)).toString());
				System.out.println((new StringBuilder(" plaintext: ")).append(toString(pt)).toString());
				System.out.println((new StringBuilder("  computed: ")).append(toString(tmp)).toString());
			}

			key = makeKey(fromString(kat[0][0]));
			String strSrc = "a ÖÐ¹úµØ´óÎï²©ÖÐ¹ú";
			String strCpt = "";
			String strDes = "";
			int intSrc = strSrc.getBytes().length;
			int intTemp;
			if (intSrc % 8 == 0)
				intTemp = 0;
			else
				intTemp = 1;
			int intBlock = intSrc / 8 + intTemp;
			byte bytBlock[] = new byte[intBlock * 8];
			bytBlock = strSrc.getBytes();
			byte oBlock[] = new byte[8];
			for (int iBlock = 0; iBlock < intBlock; iBlock++)
			{
				int intpt;
				if (iBlock < intBlock - 1 || intSrc % 8 == 0)
					intpt = 8;
				else
					intpt = intSrc % 8;
				if (intpt < 8)
				{
					for (int ipt = 0; ipt < 8; ipt++)
						oBlock[ipt] = fromString("00")[0];

				}
				for (int iob = 0; iob < intpt; iob++)
					oBlock[iob] = bytBlock[iBlock * 8 + iob];

				byte bytCpt[] = blockEncrypt(oBlock, 0, key);
				strCpt = (new StringBuilder(String.valueOf(strCpt))).append(toString(bytCpt)).toString();
			}

			System.out.println((new StringBuilder("Ô­: ")).append(strSrc).toString());
			System.out.println((new StringBuilder("ÃÜ: ")).append(strCpt).toString());
			intSrc = strCpt.length();
			intBlock = intSrc / 16;
			bytBlock = new byte[intSrc / 2];
			String strBlockc[] = new String[intBlock];
			for (int iBlock = 0; iBlock < intBlock; iBlock++)
			{
				strBlockc[iBlock] = strCpt.substring(iBlock * 16, iBlock * 16 + 16);
				ct = fromString(strBlockc[iBlock]);
				tmp = blockDecrypt(ct, 0, key);
				for (int iob = 0; iob < 8; iob++)
					bytBlock[iBlock * 8 + iob] = tmp[iob];

			}

			strDes = new String(bytBlock);
			System.out.println((new StringBuilder("½â: ")).append(strDes.trim()).toString());
		}
		catch (Exception x)
		{
			System.out.println((new StringBuilder("´íÎó: ")).append(x.getMessage()).toString());
		}
	}

	protected static final void encrypt(byte io[], Object sessionKey)
	{
		int L_R[] = {
			io[0] & 0xff | (io[1] & 0xff) << 8 | (io[2] & 0xff) << 16 | (io[3] & 0xff) << 24, io[4] & 0xff | (io[5] & 0xff) << 8 | (io[6] & 0xff) << 16 | (io[7] & 0xff) << 24
		};
		encrypt(L_R, sessionKey);
		int L = L_R[0];
		int R = L_R[1];
		io[0] = (byte)L;
		io[1] = (byte)(L >> 8);
		io[2] = (byte)(L >> 16);
		io[3] = (byte)(L >> 24);
		io[4] = (byte)R;
		io[5] = (byte)(R >> 8);
		io[6] = (byte)(R >> 16);
		io[7] = (byte)(R >> 24);
	}

	protected static final void encrypt(int io[], Object sessionKey)
	{
		int sKey[] = (int[])sessionKey;
		int L = io[0];
		int R = io[1];
		int u = R << 1 | R >>> 31;
		R = L << 1 | L >>> 31;
		L = u;
		int n = sKey.length;
		for (int i = 0; i < n;)
		{
			u = R ^ sKey[i++];
			int t = R ^ sKey[i++];
			t = t >>> 4 | t << 28;
			L ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
			u = L ^ sKey[i++];
			t = L ^ sKey[i++];
			t = t >>> 4 | t << 28;
			R ^= SP_TRANS[0x40 | t & 0x3f] | SP_TRANS[0xc0 | t >>> 8 & 0x3f] | SP_TRANS[0x140 | t >>> 16 & 0x3f] | SP_TRANS[0x1c0 | t >>> 24 & 0x3f] | SP_TRANS[u & 0x3f] | SP_TRANS[0x80 | u >>> 8 & 0x3f] | SP_TRANS[0x100 | u >>> 16 & 0x3f] | SP_TRANS[0x180 | u >>> 24 & 0x3f];
		}

		io[0] = L >>> 1 | L << 31;
		io[1] = R >>> 1 | R << 31;
	}

	protected static int engineBlockSize()
	{
		return 8;
	}

	protected static final void FP(byte io[])
	{
		int L_R[] = {
			io[0] & 0xff | (io[1] & 0xff) << 8 | (io[2] & 0xff) << 16 | (io[3] & 0xff) << 24, io[4] & 0xff | (io[5] & 0xff) << 8 | (io[6] & 0xff) << 16 | (io[7] & 0xff) << 24
		};
		FP(L_R);
		int L = L_R[0];
		int R = L_R[1];
		io[0] = (byte)L;
		io[1] = (byte)(L >> 8);
		io[2] = (byte)(L >> 16);
		io[3] = (byte)(L >> 24);
		io[4] = (byte)R;
		io[5] = (byte)(R >> 8);
		io[6] = (byte)(R >> 16);
		io[7] = (byte)(R >> 24);
	}

	protected static final void FP(int io[])
	{
		int L = io[0];
		int R = io[1];
		int t = (R >>> 1 ^ L) & 0x55555555;
		L ^= t;
		R ^= t << 1;
		t = (L >>> 8 ^ R) & 0xff00ff;
		R ^= t;
		L ^= t << 8;
		t = (R >>> 2 ^ L) & 0x33333333;
		L ^= t;
		R ^= t << 2;
		t = (L >>> 16 ^ R) & 0xffff;
		R ^= t;
		L ^= t << 16;
		t = (R >>> 4 ^ L) & 0xf0f0f0f;
		io[0] = L ^ t;
		io[1] = R ^ t << 4;
	}

	protected static final int fromDigit(char ch)
	{
		if (ch >= '0' && ch <= '9')
			return ch - 48;
		if (ch >= 'A' && ch <= 'F')
			return (ch - 65) + 10;
		if (ch >= 'a' && ch <= 'f')
			return (ch - 97) + 10;
		else
			throw new IllegalArgumentException((new StringBuilder("Invalid hex digit '")).append(ch).append("'").toString());
	}

	protected static final byte[] fromString(String hex)
	{
		int len = hex.length();
		byte buf[] = new byte[(len + 1) / 2];
		int i = 0;
		int j = 0;
		if (len % 2 == 1)
			buf[j++] = (byte)fromDigit(hex.charAt(i++));
		while (i < len) 
			buf[j++] = (byte)(fromDigit(hex.charAt(i++)) << 4 | fromDigit(hex.charAt(i++)));
		return buf;
	}

	protected static final void IP(byte io[])
	{
		int L_R[] = {
			io[0] & 0xff | (io[1] & 0xff) << 8 | (io[2] & 0xff) << 16 | (io[3] & 0xff) << 24, io[4] & 0xff | (io[5] & 0xff) << 8 | (io[6] & 0xff) << 16 | (io[7] & 0xff) << 24
		};
		IP(L_R);
		int L = L_R[0];
		int R = L_R[1];
		io[0] = (byte)L;
		io[1] = (byte)(L >> 8);
		io[2] = (byte)(L >> 16);
		io[3] = (byte)(L >> 24);
		io[4] = (byte)R;
		io[5] = (byte)(R >> 8);
		io[6] = (byte)(R >> 16);
		io[7] = (byte)(R >> 24);
	}

	protected static final void IP(int io[])
	{
		int L = io[0];
		int R = io[1];
		int t = (R >>> 4 ^ L) & 0xf0f0f0f;
		L ^= t;
		R ^= t << 4;
		t = (L >>> 16 ^ R) & 0xffff;
		R ^= t;
		L ^= t << 16;
		t = (R >>> 2 ^ L) & 0x33333333;
		L ^= t;
		R ^= t << 2;
		t = (L >>> 8 ^ R) & 0xff00ff;
		R ^= t;
		L ^= t << 8;
		t = (R >>> 1 ^ L) & 0x55555555;
		io[0] = L ^ t;
		io[1] = R ^ t << 1;
	}

	public  String dateToTimeStamp(String time){                                
        String timeStamp="";                                                          
        if(time == null || "".equals(time.toString())){                               
            return  timeStamp;                                                        
        }                                                                             
        SimpleDateFormat simpleDateFormat =new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        Date date;                                                                    
        
        try {                                                                         
            date = simpleDateFormat.parse(time);                                      
            long timeStemp = date.getTime();
            timeStamp=""+timeStemp;
        } catch (ParseException e) {                                                  
            e.printStackTrace();
        }   
        return timeStamp;                                                             
    }  

    public  String timeStampToDate(String timestampString){
        String date="";
        if(timestampString == null || "".equals(timestampString.toString())){
            return  date;
        }   
        Long timestamp = Long.parseLong(timestampString);        
        date = new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss").format(new java.util.Date(timestamp));    
        return date;           
    }   

   public  Long dateDiff(String type,String startTime, String endTime) {
        SimpleDateFormat sd = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        if(type.equalsIgnoreCase("day")){
            sd = new SimpleDateFormat("yyyy-MM-dd");
        }
        long msec_d = 1000 * 24 * 60 * 60;// 一天的毫秒数
        long msec_h = 1000 * 60 * 60;// 一小时的毫秒数
        long msec_m = 1000 * 60;// 一分钟的毫秒数
        long msec_s = 1000;// 一秒钟的毫秒数
        long diff = 0;
        long day = 0;
        long hour = 0;
        long minute = 0;
        long second = 0;
        // 获得两个时间的毫秒时间差异
        try {
            diff = sd.parse(endTime).getTime() - sd.parse(startTime).getTime();
            day = diff / msec_d;// 计算差多少天
            hour = diff % msec_d / msec_h + day * 24;// 计算差多少小时
            minute = diff % msec_d % msec_h / msec_m + day * 24 * 60;// 计算差多少分钟
            second = diff % msec_d % msec_h % msec_m / msec_s;// 计算差多少秒
        } catch (ParseException e) {
            e.printStackTrace();
        }
        if (type.equalsIgnoreCase("day")) {
            return day;
        } else if (type.equalsIgnoreCase("hour")) {
            return hour;
        } else if (type.equalsIgnoreCase("minute")) {
            return minute;
        } else if (type.equalsIgnoreCase("second")) {
            return second;
        }else{
            return diff;
        }
    }

   public String getCurrentDate(){
		   SimpleDateFormat df = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");//设置日期格式
		return df.format(new Date());
	}

	public static void main(String args[])
	{
		DES_Algorithm desa = new DES_Algorithm();
		//String sUserId = "22100";
         // String sUserToken = desa.stringEnc(sUserId, "F56AF3606BA244349528C5A93B9AE88A");
          //SimpleDateFormat simpleDateFormat =new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
          //long lFWQTime=Long.parseLong(desa.dateToTimeStamp(simpleDateFormat.format(new Date())));
		  //System.out.println(lFWQTime);
          //sUserToken=lFWQTime+sUserToken;
		  //System.out.println(sUserToken);
          //sUserToken=desa.stringEnc(sUserToken, "8C83FFF50AFF432098692906DCD1B8AD");
String sUserToken = "8FAD7B6D33DAEACA807B4BBD3A2D0ED682E2F86706671F3C28EBC014D43A521E";
            sUserToken=desa.stringDec(sUserToken, "8C83FFF50AFF432098692906DCD1B8AD");
            long diffDay = desa.dateDiff("day", desa.timeStampToDate(sUserToken.substring(0, 13)), desa.getCurrentDate());
            System.out.println("登录时差为："+diffDay+"天");
            if(diffDay>=0&&diffDay<=15){
                String sUserId=desa.stringDec(sUserToken.substring(13, sUserToken.length()), "F56AF3606BA244349528C5A93B9AE88A");
                //return sUserId;
				System.out.println(sUserId);
            }  
	}

	protected static synchronized Object makeKey(byte k[])
		throws InvalidKeyException
	{
		int i = 0;
		int L = k[i++] & 0xff | (k[i++] & 0xff) << 8 | (k[i++] & 0xff) << 16 | (k[i++] & 0xff) << 24;
		int R = k[i++] & 0xff | (k[i++] & 0xff) << 8 | (k[i++] & 0xff) << 16 | (k[i++] & 0xff) << 24;
		int t = (R >>> 4 ^ L) & 0xf0f0f0f;
		L ^= t;
		R ^= t << 4;
		t = (L << 18 ^ L) & 0xcccc0000;
		L ^= t ^ t >>> 18;
		t = (R << 18 ^ R) & 0xcccc0000;
		R ^= t ^ t >>> 18;
		t = (R >>> 1 ^ L) & 0x55555555;
		L ^= t;
		R ^= t << 1;
		t = (L >>> 8 ^ R) & 0xff00ff;
		R ^= t;
		L ^= t << 8;
		t = (R >>> 1 ^ L) & 0x55555555;
		L ^= t;
		R ^= t << 1;
		R = (R & 0xff) << 16 | R & 0xff00 | (R & 0xff0000) >>> 16 | (L & 0xf0000000) >>> 4;
		L &= 0xfffffff;
		int j = 0;
		int sKey[] = new int[32];
		for (i = 0; i < 16; i++)
		{
			if ((32508 >> i & 1) == 1)
			{
				L = (L >>> 2 | L << 26) & 0xfffffff;
				R = (R >>> 2 | R << 26) & 0xfffffff;
			} else
			{
				L = (L >>> 1 | L << 27) & 0xfffffff;
				R = (R >>> 1 | R << 27) & 0xfffffff;
			}
			int s = SKB[L & 0x3f] | SKB[0x40 | (L >>> 6 & 3 | L >>> 7 & 0x3c)] | SKB[0x80 | (L >>> 13 & 0xf | L >>> 14 & 0x30)] | SKB[0xc0 | (L >>> 20 & 1 | L >>> 21 & 6 | L >>> 22 & 0x38)];
			t = SKB[0x100 | R & 0x3f] | SKB[0x140 | (R >>> 7 & 3 | R >>> 8 & 0x3c)] | SKB[0x180 | R >>> 15 & 0x3f] | SKB[0x1c0 | (R >>> 21 & 0xf | R >>> 22 & 0x30)];
			sKey[j++] = t << 16 | s & 0xffff;
			s = s >>> 16 | t & 0xffff0000;
			sKey[j++] = s << 4 | s >>> 28;
		}

		return sKey;
	}

	public static boolean self_test()
	{
		boolean ok = false;
		try
		{
			byte kb[] = new byte[8];
			byte pt[] = new byte[8];
			for (int i = 0; i < 8; i++)
				kb[i] = (byte)i;

			for (int i = 0; i < 8; i++)
				pt[i] = (byte)i;

			Object key = makeKey(kb);
			byte ct[] = blockEncrypt(pt, 0, key);
			byte tmp[] = blockDecrypt(ct, 0, key);
			ok = areEqual(pt, tmp);
			if (!ok)
				throw new RuntimeException("Symmetric operation in ECB mode failed");
			String kat[][] = {
				{
					"0101010101010101", "95f8a5e5dd31d900", "8000000000000000"
				}, {
					"0101010101010101", "dd7f121ca5015619", "4000000000000000"
				}, {
					"0101010101010101", "2e8653104f3834ea", "2000000000000000"
				}, {
					"0123456789abcdef", "0123456789abcde7", "c95744256a5ed31d"
				}, {
					"0123456710325476", "89abcdef98badcfe", "f0148eff050b2716"
				}
			};
			for (int i = 0; i < kat.length;)
			{
				key = makeKey(fromString(kat[i][0]));
				pt = fromString(kat[i][1]);
				ct = fromString(kat[i][2]);
				i++;
				tmp = blockEncrypt(pt, 0, key);
				ok = areEqual(ct, tmp);
				if (!ok)
					throw new RuntimeException((new StringBuilder("ECB mode encryption #")).append(i).append(" failed").toString());
				tmp = blockDecrypt(ct, 0, key);
				ok = areEqual(pt, tmp);
				if (!ok)
					throw new RuntimeException((new StringBuilder("ECB mode decryption #")).append(i).append(" failed").toString());
			}

		}
		catch (Exception exception) { }
		return ok;
	}

	public String stringDec(String strCpt, String strKey)
	{
		String strDes = "";
		try
		{
			int intKey = strKey.getBytes().length;
			int intTemp;
			if (intKey % 8 == 0)
				intTemp = 0;
			else
				intTemp = 1;
			byte bytKeyAll[] = new byte[(intKey / 8 + intTemp) * 8];
			bytKeyAll = strKey.getBytes();
			byte bytKey[] = new byte[8];
			if (intKey < 8)
			{
				for (int iob = 0; iob < 8; iob++)
					bytKey[iob] = fromString("00")[0];

				for (int iob = 0; iob < intKey; iob++)
					bytKey[iob] = bytKeyAll[iob];

			} else
			{
				for (int iob = 0; iob < 8; iob++)
					bytKey[iob] = bytKeyAll[iob];

			}
			Object key = makeKey(bytKey);
			int intCpt = strCpt.length();
			int intBlock = intCpt / 16;
			byte bytBlock[] = new byte[intCpt / 2];
			String strBlock[] = new String[intBlock];
			byte tmp[] = new byte[8];
			for (int iBlock = 0; iBlock < intBlock; iBlock++)
			{
				strBlock[iBlock] = strCpt.substring(iBlock * 16, iBlock * 16 + 16);
				tmp = blockDecrypt(fromString(strBlock[iBlock]), 0, key);
				for (int iob = 0; iob < 8; iob++)
					bytBlock[iBlock * 8 + iob] = tmp[iob];

			}

			strDes = new String(bytBlock);
			strDes = strDes.trim();
		}
		catch (Exception e)
		{
			strDes = (new StringBuilder("error:")).append(e.getMessage()).toString();
		}
		return strDes;
	}

	public String stringEnc(String strSrc, String strKey)
	{
		String strCpt = "";
		try
		{
			int intKey = strKey.getBytes().length;
			int intTemp;
			if (intKey % 8 == 0)
				intTemp = 0;
			else
				intTemp = 1;
			byte bytKeyAll[] = new byte[(intKey / 8 + intTemp) * 8];
			bytKeyAll = strKey.getBytes();
			byte bytKey[] = new byte[8];
			if (intKey < 8)
			{
				for (int iob = 0; iob < 8; iob++)
					bytKey[iob] = fromString("00")[0];

				for (int iob = 0; iob < intKey; iob++)
					bytKey[iob] = bytKeyAll[iob];

			} else
			{
				for (int iob = 0; iob < 8; iob++)
					bytKey[iob] = bytKeyAll[iob];

			}
			Object key = makeKey(bytKey);
			int intSrc = strSrc.getBytes().length;
			if (intSrc % 8 == 0)
				intTemp = 0;
			else
				intTemp = 1;
			int intBlock = intSrc / 8 + intTemp;
			byte bytBlock[] = new byte[intBlock * 8];
			bytBlock = strSrc.getBytes();
			byte oBlock[] = new byte[8];
			for (int iBlock = 0; iBlock < intBlock; iBlock++)
			{
				int intpt;
				if (iBlock < intBlock - 1 || intSrc % 8 == 0)
					intpt = 8;
				else
					intpt = intSrc % 8;
				if (intpt < 8)
				{
					for (int ipt = 0; ipt < 8; ipt++)
						oBlock[ipt] = fromString("00")[0];

				}
				for (int iob = 0; iob < intpt; iob++)
					oBlock[iob] = bytBlock[iBlock * 8 + iob];

				byte bytCpt[] = blockEncrypt(oBlock, 0, key);
				strCpt = (new StringBuilder(String.valueOf(strCpt))).append(toString(bytCpt)).toString();
			}

		}
		catch (Exception e)
		{
			strCpt = (new StringBuilder("error:")).append(e.getMessage()).toString();
		}
		return strCpt;
	}

	protected static final String toString(byte ba[])
	{
		int length = ba.length;
		char buf[] = new char[length * 2];
		int i = 0;
		int j = 0;
		while (i < length) 
		{
			int k = ba[i++];
			buf[j++] = HEX_DIGITS[k >>> 4 & 0xf];
			buf[j++] = HEX_DIGITS[k & 0xf];
		}
		return new String(buf);
	}

	static void trace(String s1)
	{
	}

	static void trace(boolean flag, String s1)
	{
	}

	static 
	{
		SKB = new int[512];
		SP_TRANS = new int[512];
		String cd = "D]PKESYM`UBJ\\@RXA`I[T`HC`LZQ\\PB]TL`[C`JQ@Y`HSXDUIZRAM`EK";
		int count = 0;
		int offset = 0;
		for (int i = 0; i < cd.length(); i++)
		{
			int s = cd.charAt(i) - 64;
			if (s != 32)
			{
				int bit = 1 << count++;
				for (int j = 0; j < 64; j++)
					if ((bit & j) != 0)
						SKB[offset + j] |= 1 << s;

				if (count == 6)
				{
					offset += 64;
					count = 0;
				}
			}
		}

		String spt = "g3H821:80:H03BA0@N1290BAA88::3112aIH8:8282@0@AH0:1W3A8P810@22;22A18^@9H9@129:<8@822`?:@0@8PH2H81A19:G1@03403A0B1;:0@1g192:@919AA0A109:W21492H@0051919811:215011139883942N8::3112A2:31981jM118::A101@I88:1aN0<@030128:X;811`920:;H0310D1033@W980:8A4@804A3803o1A2021B2:@1AH023GA:8:@81@@12092B:098042P@:0:A0HA9>1;289:@1804:40Ph=1:H0I0HP0408024bC9P8@I808A;@0@0PnH0::8:19J@818:@iF0398:8A9H0<13@001@11<8;@82B01P0a2989B:0AY0912889bD0A1@B1A0A0AB033O91182440A9P8@I80n@1I03@1J828212A`A8:12B1@19A9@9@8^B:0@H00<82AB030bB840821Q:8310A302102::A1::20A1;8";
		offset = 0;
		for (int i = 0; i < 32; i++)
		{
			int k = -1;
			int bit = 1 << i;
			for (int j = 0; j < 32; j++)
			{
				int c = spt.charAt(offset >> 1) - 48 >> (offset & 1) * 3 & 7;
				offset++;
				if (c < 5)
				{
					k += c + 1;
					SP_TRANS[k] |= bit;
				} else
				{
					int param = spt.charAt(offset >> 1) - 48 >> (offset & 1) * 3 & 7;
					offset++;
					if (c == 5)
					{
						k += param + 6;
						SP_TRANS[k] |= bit;
					} else
					if (c == 6)
					{
						k += (param << 6) + 1;
						SP_TRANS[k] |= bit;
					} else
					{
						k += param << 6;
						j--;
					}
				}
			}

		}

	}
}
