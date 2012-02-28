package ua.org.vladu.povidom;

import ua.org.vladu.povidom.tempPhoto;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.HttpMultipartMode;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.entity.mime.content.StringBody;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.json.JSONException;
import org.json.JSONObject;


import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.res.Configuration;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.location.LocationListener;
import android.location.LocationManager;
import android.location.LocationProvider;
import android.net.ConnectivityManager;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.QuickContactBadge;
import android.widget.TextView;
import android.widget.Toast;

public class povidom extends Activity implements OnClickListener
{
	QuickContactBadge badgeSmall;
	Button startButton; 
	TextView text;
	String s5 = "";  String st1 = null, st2=null; LocationManager lm; LocationListener ll; 
	ProgressDialog pd; int isSet = 0; int res0 = 0; int number = 0;   List<NameValuePair> nameValuePairs;
	private JSONObject jObject; File f1 ;
	private JSONObject jObject0;
	PhonebookAdapter adapter; ListView list; List<Phonebook> listOfPhonebook;
	PhonebookAdapter adapter1; ListView list1; List<Phonebook> listOfPhonebook1;

	int prevOrientation;
	int reportId0;

	ImageView add;
	
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) 
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.povidom);
        list = (ListView)findViewById(R.id.ListView01);
        listOfPhonebook = new ArrayList<Phonebook>();
        reports r0 = new reports(this.getBaseContext());
        SQLiteDatabase sqlDb1 = r0.getReadableDatabase();
        Cursor curs = sqlDb1.rawQuery("select * from r0 order by STATUS, ID desc;", null);
       
        if (curs.getCount() > 0)
        {
        	for (int i = 0; i<curs.getCount(); i++)
        	{
        		curs.moveToPosition(i);
        		int id = curs.getInt(0);
        		String txt = curs.getString(1);
        		String url = curs.getString(2);
                String status = curs.getString(7);
                String date= curs.getString(8);
                String drophash= curs.getString(9);
                Long dateuntilremove= curs.getLong(10);
                String city = curs.getString(11);
                String street = curs.getString(12);
                listOfPhonebook.add(new Phonebook(id, txt,url,status, date, drophash, dateuntilremove, city, street));
        	}
        }
        curs.close();
        sqlDb1.close();
        
        tempPhoto tmp = new tempPhoto(this.getBaseContext());
        SQLiteDatabase sqlDb = tmp.getWritableDatabase(); 
        sqlDb.execSQL("delete from img");
        sqlDb.close();

        adapter = new PhonebookAdapter(this, listOfPhonebook);
        list.setAdapter(adapter);
        
        add = (ImageView) findViewById(R.id.add1);
        add.setOnClickListener(this);
		
		
		
		//startButton = (Button) this.findViewById(R.id.CreateButton);
		//startButton.setOnClickListener(this);
		/*startButton.setOnClickListener(new OnClickListener() {
			@Override
			public void onClick(View v) {
				mQuickAction.show(v);
				mQuickAction.setAnimStyle(QuickAction.ANIM_GROW_FROM_CENTER);
			}
		});*/
    }
    
    protected void onResume()
    {
    	super.onResume();
    	reload();         
        tempPhoto tmp = new tempPhoto(this.getBaseContext());
        SQLiteDatabase sqlDb = tmp.getWritableDatabase(); 
        sqlDb.execSQL("delete from img");
        sqlDb.close();
    }

   
    private void printProvider(String provider) 
    {
		LocationProvider info = lm.getProvider(provider);
		Toast.makeText(povidom.this, info.toString() + "\n\n", Toast.LENGTH_LONG).show();
	}
    
    @Override
    public void onConfigurationChanged(Configuration newConfig) 
    {
        super.onConfigurationChanged(newConfig);
        setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
    }

	public void onClick(View v) 
	{
		//GPS 
    	//lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
        //if ( !lm.isProviderEnabled( LocationManager.GPS_PROVIDER ) )
        //{
        //	Toast.makeText(povidom.this, R.string.checkGps, Toast.LENGTH_LONG).show();
       // 	Intent intent = new Intent(android.provider.Settings.ACTION_LOCATION_SOURCE_SETTINGS);
       // 	startActivity(intent);
       // }
     //   else
     //   {  		
        	String FILENAME = "settings";
        	String string = "1";
        	FileOutputStream fos;
        	try 
        	{
        		fos = openFileOutput(FILENAME, Context.MODE_PRIVATE);
        		fos.write(string.getBytes());
        		fos.close();
        	} catch (FileNotFoundException e) 
        	{
        		e.printStackTrace();
        	} catch (IOException e) 
        	{
			e.printStackTrace();
        	}
        	Intent intent = new Intent(povidom.this, create.class);
        	startActivity(intent);
     //   } 
	}
	
	
	public int syncronise(int reportId) 
	{
		reports r0 = new reports(this.getBaseContext());
	    SQLiteDatabase sqlDb1 = r0.getReadableDatabase();
	    Cursor curs = sqlDb1.rawQuery("select * from r0 where ID='" + String.valueOf(reportId) + "';", null);
	    if (curs.getCount() > 0)
	    {
	    	ConnectivityManager connec =  (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);
	    	if (connec.getActiveNetworkInfo().isConnected()) //      ConnectivityManager.TYPE_WIFI   
    		{
	    		pd = ProgressDialog.show(povidom.this, "", "Зачекайте..." , true, false);
	        	curs.moveToFirst();
	        	int id = curs.getInt(0);
	        	String txt = curs.getString(1);
	        	String url = curs.getString(2);
	        	String lat = curs.getString(3);
	        	String lon = curs.getString(4);
	        	String category = curs.getString(5);
	            String status = curs.getString(7);
	            String date= curs.getString(8);
	            String city = curs.getString(11);
	            String street = curs.getString(12);
	            prevOrientation = getRequestedOrientation();
	            if(getResources().getConfiguration().orientation == Configuration.ORIENTATION_LANDSCAPE) 
	            {
	            	setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
	            } else if(getResources().getConfiguration().orientation == Configuration.ORIENTATION_PORTRAIT) 
	            {
	            	setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);
	            } else 
	            {
	            	setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_NOSENSOR);
	            }
   		       	Send send = new Send(id, lat, lon, category, txt);
   		       	httpThread httpthread = new httpThread(send);
     		    httpthread.start();
	                 
     		    curs.close();
     		    sqlDb1.close();
     		    return 0;
	    	}
	    	else 
	    	{
	    		Toast.makeText(povidom.this, R.string.checkConnection , Toast.LENGTH_LONG).show();
	    		return -1;
	    	}
	    }
	    else 
	    {
	    	Toast.makeText(povidom.this, R.string.noElementsToSync, Toast.LENGTH_LONG).show();
	        return -1;
	    } 
	}
	
	// setting up menus
    
    @Override
	public boolean onCreateOptionsMenu(Menu menu) 
    {
	    MenuInflater inflater = getMenuInflater();
	    inflater.inflate(R.menu.main, menu);
	    return true;
	}
	
	
	@Override
	public boolean onOptionsItemSelected(MenuItem item) 
	{
	   switch (item.getItemId()) 
	   {
	   		case R.id.addMenu:
	   			//GPS 
	   			/*lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
			    if ( !lm.isProviderEnabled( LocationManager.GPS_PROVIDER ) )
			    {
			    	Toast.makeText(povidom.this, R.string.checkGps, Toast.LENGTH_LONG).show();
			    }
			    else
			    {*/
			    	String FILENAME = "settings";
			    	String string = "1";
			    	FileOutputStream fos;
			    	try 
			    	{
			    		fos = openFileOutput(FILENAME, Context.MODE_PRIVATE);
			    		fos.write(string.getBytes());
			    		fos.close();
			    	} catch (FileNotFoundException e) 
			    	{
			    	e.printStackTrace();
			    	} catch (IOException e) 
			    	{
			    		e.printStackTrace();
			    	}
			    Intent intent = new Intent(povidom.this, create.class);
			    startActivity(intent);
			    //}
			    return true;
	   		default:
	   			return super.onOptionsItemSelected(item);
	    	}
		}
	
	 	public class reports extends SQLiteOpenHelper 
	 	{
		    private static final int DATABASE_VERSION = 2;
		    private static final String DICTIONARY_TABLE_NAME = "r0";
		    private static final String DICTIONARY_TABLE_CREATE =
		                "CREATE TABLE " + DICTIONARY_TABLE_NAME + " (" +
		                "ID" + " integer primary key autoincrement, " +
		                "TXT" + " TEXT, " +
		                "URL" + " TEXT, " +
		                "LATITUDE" + " TEXT, " +
		                "LONGTITUDE" + " TEXT, " +
		                "CATEGORY" + " TEXT, " +
		                "IMAGE" + " BLOB, " +
		                "STATUS" + " TEXT, " +
		                "DATEOFCREATION" + " TEXT, " +
		                "DROPHASH" + " TEXT, " +
		                "DATEUNTILREMOVE" + " REAL, " +
		                "CITY" + " TEXT, " +
		                "STREET" + " TEXT);";
			private static final String DATABASE_NAME = "reports30";

		    reports(Context context) 
		    {
		        super(context, DATABASE_NAME, null, DATABASE_VERSION);
		    }

		    @Override
		    public void onCreate(SQLiteDatabase db) 
		    {
		        db.execSQL(DICTIONARY_TABLE_CREATE);
		    }

			@Override
			public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) 
			{
				// TODO Auto-generated method stub
			}
		}
	
	 	public class MyTask extends AsyncTask<Void, Void, Void> 
	 	{
	 		private ProgressDialog progress1;
	 		public MyTask(ProgressDialog progress) 
	 		{
	 			this.progress1 = progress;
	 		}
	 		public void onPreExecute() 
	 		{
	 			progress1.show();
	 		}
	 		public void onPostExecute(Void unused) 
	 		{
	 			progress1.dismiss();
	 		}

	 		@Override
	 		protected Void doInBackground(Void... params) 
	 		{
	 			String t = "1" + "2";			
	 			return null;
	 		}
		}	 
	 
	 	public class PhonebookAdapter extends BaseAdapter implements OnClickListener 
	 	{
		    private Context context;
		    private List<Phonebook> listPhonebook;
		    public PhonebookAdapter(Context context, List<Phonebook> listPhonebook) 
		    {
		        this.context = context;
		        this.listPhonebook = listPhonebook;
		    }

		    public int getCount() 
		    {
		        return listPhonebook.size();
		    }

		    public Object getItem(int position) 
		    {
		        return listPhonebook.get(position);
		    }

		    public long getItemId(int position) 
		    {
		        return position;
		    }

		    public View getView(int position, View convertView, ViewGroup viewGroup) 
		    {
		        final Phonebook entry = listPhonebook.get(position);	        
		        if (convertView == null) 
		        {
		            LayoutInflater inflater = (LayoutInflater) context
		                    .getSystemService(Context.LAYOUT_INFLATER_SERVICE);
		            convertView = inflater.inflate(R.layout.phone_row, null);
		        }
		        
		        reportId0 = entry.getId();
		        final String url = entry.getUrl();
		        final String message = entry.getTxt();
		        final String drophash = entry.getDrophash();
		        final Long dateuntilremove = entry.getDateuntilremove();
		        
		        convertView.setOnClickListener(new OnClickListener() {
					public void onClick(View v) {
						//Toast.makeText(getApplicationContext(), String.valueOf(entry.getId()), Toast.LENGTH_LONG).show();
						
				        if (entry.getStatus().compareTo("saved") == 0)
				        {
						 //Add action item
		    	        ActionItem addAction = new ActionItem();
		    			
		    			addAction.setTitle("Синхронізувати");
		    			addAction.setIcon(getResources().getDrawable(R.drawable.sync_icc));

		    			//Upload action item
		    			ActionItem upAction = new ActionItem();
		    			
		    			upAction.setTitle("Видалити");
		    			upAction.setIcon(getResources().getDrawable(R.drawable.delete_icc));
		    			
		    			final QuickAction mQuickAction 	= new QuickAction(povidom.this);
		    			
		    			mQuickAction.addActionItem(addAction);
		    			mQuickAction.addActionItem(upAction);
		    			
		    			//setup the action item click listener
		    			mQuickAction.setOnActionItemClickListener(new QuickAction.OnActionItemClickListener() {			
		    				public void onItemClick(int pos) {
		    					
		    					if (pos == 0) { //Add item selected
		    						ConnectivityManager connec =  (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);
					        		if (connec.getNetworkInfo(ConnectivityManager.TYPE_WIFI).isConnected() || connec.getNetworkInfo(ConnectivityManager.TYPE_MOBILE).isConnected()) //      ConnectivityManager.TYPE_WIFI   
					      			{
					        			syncronise(entry.getId());
					      			}
					        		else
					        		{
					        			Toast.makeText(povidom.this, R.string.checkConnection , Toast.LENGTH_LONG).show();
					        		}
		    					} else if (pos == 1) { //Accept item selected
		    						AlertDialog.Builder builder = new AlertDialog.Builder(povidom.this);
					            	builder.setMessage("Справді хочете видалити звіт?")
					            	       .setCancelable(false)
					            	       .setPositiveButton("Так", new DialogInterface.OnClickListener() {
					            	           public void onClick(DialogInterface dialog, int id) {
					  			        		 listPhonebook.remove(entry);
								        	     notifyDataSetChanged();
								        	     Toast.makeText(povidom.this, "Звіт видалено", Toast.LENGTH_LONG).show();    
								        		reports r0 = new reports(povidom.this.getBaseContext());
										        SQLiteDatabase sqlDb = r0.getWritableDatabase(); 
										        sqlDb.execSQL("delete from r0 where ID = " + String.valueOf(reportId0));
											    sqlDb.close();	
					            	           }
					            	       })
					            	       .setNegativeButton("Ні", new DialogInterface.OnClickListener() {
					            	           public void onClick(DialogInterface dialog, int id) {
					            	                dialog.cancel();
					            	           }
					            	       });
					            	AlertDialog alert = builder.create();
					            	alert.show();	
		    					} 
		    				}
		    			});
						mQuickAction.show(v);
						mQuickAction.setAnimStyle(QuickAction.ANIM_GROW_FROM_CENTER);
				        } else if (entry.getStatus().compareTo("uploaded") == 0)
					     {
				        	final QuickAction mQuickAction 	= new QuickAction(povidom.this);
				        	
			    			//Accept action item
			    			ActionItem accAction = new ActionItem();
			    			
			    			accAction.setTitle("Поділитись");
			    			accAction.setIcon(getResources().getDrawable(R.drawable.share_icc));
			    			
			    			//Upload action item
			    			ActionItem upAction = new ActionItem();
			    			
			    			upAction.setTitle("Переглянути");
			    			upAction.setIcon(getResources().getDrawable(R.drawable.browse_icc));
			    			
			    			mQuickAction.addActionItem(accAction);
			    			mQuickAction.addActionItem(upAction);
			    			
				        	Calendar c = Calendar.getInstance(); 
				            long milliToday = c.getTimeInMillis()/ 1000L;
				            if ((milliToday - dateuntilremove) < 900 && (milliToday - dateuntilremove) > 0)
				            {
				        	 //Add action item
			    	        ActionItem addAction = new ActionItem();
			    			
			    			addAction.setTitle("Видалити");
			    			addAction.setIcon(getResources().getDrawable(R.drawable.delete_icc));
			    			mQuickAction.addActionItem(addAction);
				            }
			    			
			    			//setup the action item click listener
			    			mQuickAction.setOnActionItemClickListener(new QuickAction.OnActionItemClickListener() {			
			    				public void onItemClick(int pos) {
			    					
			    					if (pos == 0) { //Add item selected
						        		Intent share = new Intent(Intent.ACTION_SEND);
						        	    share.setType("text/*");
						        	    String msg = "";
						        	    if (entry.getTxt().length()>100)
						        	    msg = entry.getTxt().substring(0, 100);
						        	    else msg = entry.getTxt();
						        	    String url1 = msg + "... " + url;
						        	    share.putExtra(android.content.Intent.EXTRA_TEXT, url1); //new String[]{url}
						        	    startActivity(Intent.createChooser(share, "Поділитись через..."));
			    					} else if (pos == 1) { //Accept item selected
			    						Intent browserIntent = new Intent("android.intent.action.VIEW", Uri.parse(url));
						        		startActivity(browserIntent);
			    					} else if (pos == 2) { //Upload item selected
			    						AlertDialog.Builder builder = new AlertDialog.Builder(povidom.this);
						            	builder.setMessage("Справді хочете видалити звіт?")
						            	       .setCancelable(false)
						            	       .setPositiveButton("Так", new DialogInterface.OnClickListener() 
						            	       {
						            	           public void onClick(DialogInterface dialog, int id) 
						            	           {
						            	        	   ConnectivityManager connec =  (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);
										        		if (connec.getNetworkInfo(ConnectivityManager.TYPE_WIFI).isConnected() || connec.getNetworkInfo(ConnectivityManager.TYPE_MOBILE).isConnected()) //      ConnectivityManager.TYPE_WIFI   
										        		{
											        		prevOrientation = getRequestedOrientation();
											                if(getResources().getConfiguration().orientation == Configuration.ORIENTATION_LANDSCAPE) 
											                {
											                    setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
											                } else if(getResources().getConfiguration().orientation == Configuration.ORIENTATION_PORTRAIT) 
											                {
											                    setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_PORTRAIT);
											                } else 
											                {
											                    setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_NOSENSOR);
											                }
													        int n = deleteReport(drophash);
													        setRequestedOrientation(prevOrientation);
													        
													        if (n == 0)
													        {
													        	listPhonebook.remove(entry);
												        	    notifyDataSetChanged();    
														        reports r0 = new reports(povidom.this.getBaseContext());
														        SQLiteDatabase sqlDb = r0.getWritableDatabase(); 
														        sqlDb.execSQL("delete from r0 where ID = " + String.valueOf(entry.getId()));
															    sqlDb.close();
													        }
										        		}
										        		else
										        		{
										        			Toast.makeText(povidom.this, R.string.checkConnection , Toast.LENGTH_LONG).show();
										        		}
						            	           	}
						            	       	})
						            	       .setNegativeButton("Ні", new DialogInterface.OnClickListener() {
						            	           public void onClick(DialogInterface dialog, int id) {
						            	                dialog.cancel();
						            	           }
						            	       });
						            	AlertDialog alert = builder.create();
						            	alert.show();
			    					}	
			    				}
			    			});
							mQuickAction.show(v);
							mQuickAction.setAnimStyle(QuickAction.ANIM_GROW_FROM_CENTER);
					     }
					}
				});
		        TextView tvContact = (TextView) convertView.findViewById(R.id.tvContact);
		        tvContact.setText(entry.getTxt());

		        TextView tvPhone = (TextView) convertView.findViewById(R.id.tvMobile);
		        ImageView status = (ImageView) convertView.findViewById(R.id.statuspreview);
		        
		        if (entry.getStatus().compareTo("saved") == 0)
		        {
		        	tvPhone.setText(R.string.saved);
		        	status.setImageResource(R.drawable.statussaved);
		        }
		        else if (entry.getStatus().compareTo("uploaded") == 0)
			    {
		        	tvPhone.setText(R.string.uploaded);
		        	status.setImageResource(R.drawable.statusonline);
			    }

		        TextView tvMail = (TextView) convertView.findViewById(R.id.tvMail);
		        tvMail.setText(entry.getDate());
		        
		        
		        return convertView;
		    }

		    public void onClick(View view) {
		    	
		    }
		}
	 public class Phonebook 
	 {
	        private int id;
	        private String txt;
	        private String url;  
	        private String status;
	        private String date;
	        private String drophash;
	        private Long dateuntilremove;
	        private String city;
	        private String street;
	        
	        public Phonebook(int id, String txt, String url, String status, String date, String drophash, Long dateuntilremove, String city, String street) 
	        {
	                super();
	                this.id = id;
	                this.txt = txt;
	                this.url = url;
	                this.status = status;
	                this.date = date;
	                this.drophash = drophash;
	                this.dateuntilremove = dateuntilremove;
	                this.city = city;
	                this.street = street;
	        }

	        public int getId() 
	        {
	                return id;
	        }
	        public void setId(int id) 
	        {
	                this.id = id;
	        }
	        public String getTxt() 
	        {
	                return txt;
	        }
	        public void setTxt(String txt) 
	        {
	                this.txt = txt;
	        }
	        public String getUrl() 
	        {
	                return url;
	        }
	        public void setUrl(String url) 
	        {
	                this.url = url;
	        }
	        public String getStatus() 
	        {
                return status;
	        }
	        public void setStatus(String status) 
	        {
	                this.status = status;
	        }
	        public String getDate() 
	        {
	            return date;
		    }
		    public void setDate(String date) 
		    {
		            this.date = date;
		    }
	        public String getDrophash() 
	        {
	            return drophash;
		    }
		    public void setDrophash(String drophash) 
		    {
		            this.drophash = drophash;
		    }
	        public Long getDateuntilremove() 
	        {
	            return dateuntilremove;
		    }
		    public void setDateuntilremove(Long dateuntilremove) 
		    {
		            this.dateuntilremove = dateuntilremove;
		    }
	 }

	 private class Send  
	 {
		 int id;
		 String st1, st2, s5, tx;
	     public Send(int id, String st1, String st2, String s5, String tx) 
	     {
	    	 this.id = id;
	    	 this.st1 = st1;
	    	 this.st2 = st2;
	    	 this.s5 = s5;
	    	 this.tx = tx;
	     }

	     public int send()
	     {
	    	 HttpParams httpParameters = new BasicHttpParams();
	         int timeoutConnection = 5000;
	         HttpConnectionParams.setConnectionTimeout(httpParameters, timeoutConnection);
	         int timeoutSocket = 25000;
	         HttpConnectionParams.setSoTimeout(httpParameters, timeoutSocket);
	         DefaultHttpClient httpClient = new DefaultHttpClient(httpParameters);
             HttpContext localContext = new BasicHttpContext();
             HttpPost httpPost = new HttpPost("http://povidom-vladu.org.ua/index/post/");             
	         
             String path = Environment.getExternalStorageDirectory().toString();
		     String img=path+"/test.jpg";
		     OutputStream fOut = null;
		     f1 = new File(path, "test.jpg");
		     try {
		    	 fOut = new FileOutputStream(f1);
		     } catch (FileNotFoundException e) {
		    	 e.printStackTrace();
		     }
		     reports r2 = new reports(povidom.this.getBaseContext());
	         SQLiteDatabase sqlDb = r2.getReadableDatabase(); 
	               
	         Cursor curs = sqlDb.rawQuery("select IMAGE from r0 where ID = " + String.valueOf(id) + ";", null);
	         curs.moveToFirst();

	         if (curs.getCount() > 0 )
	         {
	        	 // read the image from blob field...
		        byte[] bitmapData = curs.getBlob(0);
		        curs.close();
		        sqlDb.close();
		        
		        // ... and turn it into the bitmap
		        Bitmap bmp = BitmapFactory.decodeByteArray(bitmapData, 0, bitmapData.length);
		        
		        // lets free some memory
		        bitmapData = null;
		        System.gc();

		        boolean result = bmp.compress(Bitmap.CompressFormat.JPEG, 99, fOut);
		        String s = new Boolean(result).toString();
		        Log.d("joss", new Boolean(s).toString());
	         }
	         
	         // Add your data
	         List<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>(5);
	       	 nameValuePairs.add(new BasicNameValuePair("msg",this.tx));
	       	 nameValuePairs.add(new BasicNameValuePair("lat", this.st1));
	       	 nameValuePairs.add(new BasicNameValuePair("long", this.st2));
	       	 nameValuePairs.add(new BasicNameValuePair("img", img));
	       	 nameValuePairs.add(new BasicNameValuePair("type", this.s5));
	         
	       	 try
	       	 {
	       		 MultipartEntity entity = new MultipartEntity(HttpMultipartMode.BROWSER_COMPATIBLE);
                 for(int index=0; index < nameValuePairs.size(); index++) 
                 {
                	 if(nameValuePairs.get(index).getName().equalsIgnoreCase("img")) 
	                 {
                		 entity.addPart(nameValuePairs.get(index).getName(), new FileBody(f1));
	                 } else 
	                 {
	                	 entity.addPart(nameValuePairs.get(index).getName(), new StringBody(nameValuePairs.get(index).getValue()));
	                 }
	             }
	             httpPost.setEntity(entity);
	             HttpResponse response = httpClient.execute(httpPost, localContext);  
	             f1.delete();
	   	    		    
	             String result = "";
	             InputStream in = response.getEntity().getContent();
	             BufferedReader reader = new BufferedReader(new InputStreamReader(in));
	             StringBuilder str = new StringBuilder();
	             String line = null;
	             while((line = reader.readLine()) != null)
	             {
	            	 str.append(line + "\n");
	             }
	             in.close();
	             result = str.toString();
	               
	             String status = "Null";
	             String url0 = "Null";
	             String drophash = "Null";
	             try 
	             {
	            	 jObject = new JSONObject(result);
	            	 status = jObject.getString("status");
	            	 url0 = jObject.getString("url");
	            	 drophash = jObject.getString("drophash");
	             } catch (JSONException e) 
	             {
						e.printStackTrace();
	             }  
	             if (status.compareTo("OK") == 0) //(result.substring(0, 2).compareTo("OK") == 0)
	             {
	            	 String url = url0;	               	   	 
	            	 tempPhoto tmp2 = new tempPhoto(povidom.this.getBaseContext());
		       	     SQLiteDatabase sqlDb2 = tmp2.getReadableDatabase(); 
		       	     byte[] bitmapData = null; 
		       	     Cursor curs2 = sqlDb2.rawQuery("select IMG from img;", null);
		       	     curs2.moveToFirst();
		       	     if (curs2.getCount() > 0 )
		       	     {
		       	    	 bitmapData = curs2.getBlob(0);
		       	     }
		       	     curs2.close();
		       		 sqlDb2.close();
		       		 reports r0 = new reports(povidom.this.getBaseContext());
		             SQLiteDatabase sqlDb1 = r0.getWritableDatabase();

		             long unixTime = System.currentTimeMillis() / 1000L;    
		       	     ContentValues updateCountry = new ContentValues();
		             updateCountry.put("STATUS", "uploaded");
		             updateCountry.put("URL", url);
		             updateCountry.put("IMAGE", "");
		             updateCountry.put("DATEUNTILREMOVE", unixTime);
		             updateCountry.put("DROPHASH", drophash);
		             sqlDb1.update("r0", updateCountry, "id=?", new String[] {String.valueOf(this.id)});
		       	     sqlDb1.close();
		       	     return 0;
	             }
	             else
	             {
	            	 return -1;   
	             }
	       	 } catch (IOException e) 
	       	 {
	       		 e.printStackTrace();
	         }  
	         return 1;
	    }
	}

	private class httpThread extends Thread 
	{
	        private Send send;
	        public httpThread(Send send) 
	        {
	           this.send = send;
	        }

	        @Override
	        public void run() 
	        {
	        	res0 = send.send();
	            handler.sendEmptyMessage(0);
	        }

	        private Handler handler = new Handler() 
	        {
	            @Override
	            public void handleMessage(Message msg) 
	            {
	                pd.dismiss(); 
	                setRequestedOrientation(prevOrientation);
	                if (res0 == 0)
	                {
	                	Toast.makeText(povidom.this, "Звіт завантажено. Ви можете видалити його з сервера протягом 15 хвилин", Toast.LENGTH_LONG).show();
	                } 
	                else if (res0 == -1)
	                {
	                	Toast.makeText(povidom.this, R.string.serverError, Toast.LENGTH_LONG).show();
	                }
	                reload();
	            }
	        };
	    }
	 
	 	public int deleteReport(String drophash)
	 	{
    		ConnectivityManager connec =  (ConnectivityManager)getSystemService(Context.CONNECTIVITY_SERVICE);
    		if (connec.getActiveNetworkInfo().isConnected()) //      ConnectivityManager.TYPE_WIFI   
   			{
    			HttpParams httpParameters = new BasicHttpParams();
	            int timeoutConnection = 5000;
	            HttpConnectionParams.setConnectionTimeout(httpParameters, timeoutConnection);
	            int timeoutSocket = 25000;
	            HttpConnectionParams.setSoTimeout(httpParameters, timeoutSocket);
	            DefaultHttpClient httpClient = new DefaultHttpClient(httpParameters);
	            HttpContext localContext = new BasicHttpContext();
	            HttpPost httpPost = new HttpPost("http://povidom-vladu.org.ua/index/remove/");
	               
	            // Add your data  
              	nameValuePairs = new ArrayList<NameValuePair>(1);  
       	        nameValuePairs.add(new BasicNameValuePair("drophash", drophash));                
				try 
				{
					MultipartEntity entity = new MultipartEntity(HttpMultipartMode.BROWSER_COMPATIBLE);
					for(int index=0; index < nameValuePairs.size(); index++) 
	                {
						entity.addPart(nameValuePairs.get(index).getName(), new StringBody(nameValuePairs.get(index).getValue()));
	                }
	                httpPost.setEntity(entity);
	                HttpResponse response;
					response = httpClient.execute(httpPost, localContext);							
			        String result = "";
			        InputStream in = response.getEntity().getContent();
			        BufferedReader reader = new BufferedReader(new InputStreamReader(in));
			        StringBuilder str = new StringBuilder();
			        String line = null;
			        while((line = reader.readLine()) != null)
			        {
			        	str.append(line + "\n");
			        }
			        in.close();
			        result = str.toString();
	                String status="";
	                try 
	                {
	                	jObject0 = new JSONObject(result);
						status = jObject0.getString("status");
	                } catch (JSONException e) 
	                {
								e.printStackTrace();
					} 
			        if (status.compareTo("OK") == 0) 
			        {
			        	Toast.makeText(povidom.this, "Звіт видалено з серверу", Toast.LENGTH_LONG).show();		
			        }
			        else
			        {
			        	Toast.makeText(povidom.this, "Помилка видалення звіта", Toast.LENGTH_LONG).show();
			        }
			        return 0;
				} catch (ClientProtocolException e) 
				{
					e.printStackTrace();
				} catch (IOException e) 
				{
					e.printStackTrace();
				}	    
	       	}  
       		else    
       		{
       			Toast.makeText(povidom.this, R.string.checkConnection , Toast.LENGTH_LONG).show();
       			return -1;
       		}
	 		return 0;
	 	}
	 	
		public String getDate()
		{
	        Calendar c = Calendar.getInstance(); 
	        String day = String.valueOf(c.get(Calendar.DAY_OF_MONTH));
	        String month = String.valueOf(c.get(Calendar.MONTH));
	        String year = String.valueOf(c.get(Calendar.YEAR));
	        String hours = String.valueOf(c.get(Calendar.HOUR));
	        String minutes = String.valueOf(c.get(Calendar.MINUTE));
	        String date = day + "/" + month + "/" + year + " " + hours + ":" + minutes;
	        return date;
		}
		
		 public void reload()
		 {
			 listOfPhonebook.clear();
			 adapter.notifyDataSetChanged();
			 reports r0 = new reports(this.getBaseContext());
		     SQLiteDatabase sqlDb1 = r0.getReadableDatabase();
		     Cursor curs = sqlDb1.rawQuery("select * from r0 order by STATUS, ID desc;", null);  
		     if (curs.getCount() > 0)
		     {
		    	 for (int i = 0; i<curs.getCount(); i++)
		         {
		    		 curs.moveToPosition(i);
		    		 int id = curs.getInt(0);
		    		 String txt = curs.getString(1);
		    		 String url = curs.getString(2);
		    		 String status = curs.getString(7);
		    		 String date= curs.getString(8);
		    		 String drophash= curs.getString(9);
		    		 Long dateuntilremove= curs.getLong(10);
		    		 String city = curs.getString(11);
		    		 String street = curs.getString(12);
		    		 listOfPhonebook.add(new Phonebook(id, txt,url,status, date, drophash, dateuntilremove, city, street));
		         }
		     }
		     curs.close();
		     sqlDb1.close();         
		     list.setAdapter(adapter);
		 }
}