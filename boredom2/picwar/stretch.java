// Copyright (c) 1996 Jared Smith-Mickelson http://web.mit.edu/jaredsm/www/

import java.applet.*;
import java.awt.*;
import java.awt.image.*;

public class Stretch extends Applet implements Runnable {
	Image			image, originalI, lastI, offscreenI;
	MediaTracker	tracker;
	Thread			runner;
	String			status;
	boolean			mousedown;
	Graphics		offscreenG, realG, tempG;
	int				width, height, fromX, fromY, toX, toY;
	double			fraction;
	Button			undoB, resetB;
	Panel			buttonP;

	public void init() {
		System.err.println("Stretch, by Jared Smith-Mickelson - " +
			"http://web.mit.edu/jaredsm/www/");
		mousedown = false;
		// get the image
		String imageName = getParameter("image");
		if(imageName == null)
			System.err.println("Paramter 'image' not specified");
		image = getImage(getDocumentBase(), imageName);
		tracker = new MediaTracker(this);
		tracker.addImage(image, 0);
		// set up the buttons
		undoB = new Button("Undo");
		resetB = new Button("Reset");
		buttonP = new Panel();
		buttonP.add(resetB);
		buttonP.add(undoB);
		setLayout(new BorderLayout());
		add("South", buttonP);
		try { tracker.waitForID(0); } catch (InterruptedException e) { ; }
		if(tracker.isErrorID(0))
			System.err.println("Error loading image: " + imageName);
		width = image.getWidth(this);
		height = image.getHeight(this);
		resize(width, height + buttonP.bounds().height);
		// set up offscreen image for dubble-buffering
		offscreenI = createImage(width, height);
		offscreenG = offscreenI.getGraphics();
		realG = getGraphics();
		realG.drawImage(offscreenI, 0, 0, this);
		originalI = image;
		lastI = image;
	}

	public boolean handleEvent(Event evt) {
		switch(evt.id) {
			case Event.ACTION_EVENT:
				if(evt.target == undoB) {
					Image temp = lastI;
					lastI = image;
					image = temp;
					paint(offscreenG);
					update(realG);
				} else if(evt.target == resetB) {
					lastI = image;
					image = originalI;
					paint(offscreenG);
					update(realG);
				}
				return true;
			case Event.MOUSE_DOWN:
				fromX = evt.x;
				fromY = evt.y;
				return true;
			case Event.MOUSE_UP:
				if(mousedown) {
					mousedown = false;
					render();
				}
				return true;
			case Event.MOUSE_DRAG:
				mousedown = true;
				toX = evt.x;
				toY = evt.y;
				paint(offscreenG);
				update(realG);
				return true;
		}
		return false;
	}

	public void run() {
		// Runnables must have a run() method
	}

	void render() {
		int length = width * height;
		int oldpix[] = new int[length];
		// get the current image pixel array
		PixelGrabber grabber = new PixelGrabber(image, 0, 0, width, height,
															oldpix, 0, width);
		boolean noerror = true;
		try { noerror = grabber.grabPixels(); }
		catch (InterruptedException e) { ; }
		if(!noerror)
			System.err.println("Couldn't grab pixels from image.");
		int newpix[] = new int[length];
		int index = 0;
		int	dx = toX - fromX;
		int dy = toY - fromY;
		double d = Math.sqrt(dx * dx + dy * dy);
		// remap the image by interating through the new image
		// raster (newpix[]) and pulling pixel values from the old image
		// raster (oldpix[]) acording to my nifty expodential stretch
		// formula.  it's simple really.
		for(int y = 0; y < height; y++) {
			for(int x = 0; x < width; x++, index++) {
				double c = Math.exp(-Math.sqrt((x - toX) * (x - toX) +
											(y - toY) * (y - toY)) / d);
				newpix[index] = oldpix[((index - (int)(dx * c) -
						(int)(dy * c) * width) % length + length) % length];
			}
		}
		lastI = image;
		// make the new image and display it
		image = createImage(new MemoryImageSource(width, height, newpix,
																0, width));
		tracker.addImage(image, 0);
		try { tracker.waitForID(0); } catch (InterruptedException e) { ; }
		if(tracker.isErrorID(0))
			System.err.println("Error creating new image.");
		paint(offscreenG);
		update(realG);
	}

	public void start() {
		if(runner == null) {
			runner = new Thread(this);
			runner.start();
		}
	}

	public void stop() {
		if(runner != null) {
			runner.stop();
			runner = null;
		}
	}

	public void paint(Graphics g) {
		if(g == null)
			return;
		if(mousedown) {
			// draw the stretch line
			g.drawImage(image, 0, 0, this);
			g.setColor(Color.red);
			g.drawLine(fromX - 1, fromY, toX - 1, toY);
			g.drawLine(fromX, fromY - 1, toX, toY - 1);
			g.drawLine(fromX + 1, fromY, toX + 1, toY);
			g.drawLine(fromX, fromY + 1, toX, toY + 1);
			g.setColor(Color.white);
			g.drawLine(fromX, fromY, toX, toY);
		} else
			g.drawImage(image, 0, 0, this);
	}

	public void update(Graphics g) {
		g.drawImage(offscreenI, 0, 0, this);
	}
}
