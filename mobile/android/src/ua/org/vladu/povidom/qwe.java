package ua.org.vladu.povidom;

import android.app.Activity;
import android.content.ContentValues;
import android.content.pm.ActivityInfo;
import android.content.res.Configuration;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.util.Log;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Toast;
import android.hardware.Camera;
import android.hardware.Camera.Size;

import java.io.IOException;
import java.util.List;

import ua.org.vladu.povidom.tempPhoto;

public class qwe extends Activity implements OnClickListener, SurfaceHolder.Callback, Camera.PictureCallback
{
	Camera camera;
	SurfaceView cameraView;
	SurfaceHolder surfaceHolder;
	public static final int LARGEST_WIDTH = 200;
	public static final int LARGEST_HEIGHT= 200;
	Size mPreviewSize;   
	List <Size> mSupportedPreviewSizes;

    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);

        setContentView(R.layout.main);
        Toast.makeText(qwe.this, "Доторкніться екрану для отримання фото", Toast.LENGTH_SHORT).show();
        cameraView = (SurfaceView) this.findViewById(R.id.CameraView);
        surfaceHolder = cameraView.getHolder();
        surfaceHolder.setType(SurfaceHolder.SURFACE_TYPE_PUSH_BUFFERS);
        surfaceHolder.addCallback(this);
        cameraView.setFocusable(true);
        cameraView.setFocusableInTouchMode(true);
        cameraView.setClickable(true);
        cameraView.setOnClickListener(this);

    }
	@Override
	public void surfaceChanged(SurfaceHolder holder, int format, int width,
			int height) {
		// TODO Auto-generated method stub
		
	}
	@Override
	public void surfaceCreated(SurfaceHolder holder) {
		// TODO Auto-generated method stub
		camera = Camera.open();
		try {
			Camera.Parameters parameters = camera.getParameters();
			if (this.getResources().getConfiguration().orientation !=Configuration.ORIENTATION_LANDSCAPE) {
			parameters.set("orientation", "portrait");
			parameters.setRotation(90);
			} else {
			parameters.set("orientation", "landscape");
			parameters.setRotation(0);
			}
			parameters.setJpegQuality(100);
			mSupportedPreviewSizes = camera.getParameters().getSupportedPictureSizes();
			mPreviewSize = getOptimalPreviewSize(mSupportedPreviewSizes, 480, 320);
			parameters.setPictureSize(mPreviewSize.width, mPreviewSize.height);
			camera.setParameters(parameters);
			camera.setPreviewDisplay(holder);
			} catch (IOException exception) {
			camera.release();
			Log.v("11",exception.getMessage());
			}
			camera.startPreview();
	}
	@Override
	public void surfaceDestroyed(SurfaceHolder holder) {
		// TODO Auto-generated method stub
		camera.stopPreview();
		camera.release();
	}
	@Override
	public void onPictureTaken(byte[] data, Camera camera) {
		// TODO Auto-generated method stub				
			 	tempPhoto tmp = new tempPhoto(this.getBaseContext());
		        SQLiteDatabase sqlDb = tmp.getWritableDatabase(); 
		        sqlDb.execSQL("delete from img");
		        ContentValues values = new ContentValues();
		        values.put("IMG", data);
		        long n = sqlDb.insert("img", null, values);
			    sqlDb.close();
		    	finish();
	}
	@Override
	public void onClick(View v) {
		// TODO Auto-generated method stub
		camera.takePicture(null, null, null, this);
	}

    private Size getOptimalPreviewSize(List<Size> sizes, int w, int h) {
        final double ASPECT_TOLERANCE = 0.1;
        double targetRatio = (double) w / h;
        if (sizes == null) return null;
        Size optimalSize = null;
        double minDiff = Double.MAX_VALUE;
        int targetHeight = h;
        for (Size size : sizes) {
            double ratio = (double) size.width / size.height;
            if (Math.abs(ratio - targetRatio) > ASPECT_TOLERANCE) continue;
            if (Math.abs(size.height - targetHeight) < minDiff) {
                optimalSize = size;
                minDiff = Math.abs(size.height - targetHeight);
            }
        }
        if (optimalSize == null) {
            minDiff = Double.MAX_VALUE;
            for (Size size : sizes) {
                if (Math.abs(size.height - targetHeight) < minDiff) {
                    optimalSize = size;
                    minDiff = Math.abs(size.height - targetHeight);
                }
            }
        }
        return optimalSize;
    }
}
